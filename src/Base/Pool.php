<?php
/**
 * 对象池
 */
namespace EzHttp\Base;

class Pool
{
    /**
     * @var Pool 对象池单例
     */
    private static $instance;

    /**
     * @var array
     */
    public $map;

    private function __construct()
    {
        $this->map = [];
    }

    /**
     * 私有克隆方法防止对象配克隆git
     */
    private function __clone()
    {
    }

    /**
     * 获取对象池单例
     * @return Pool
     */
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取对象
     * @param $class
     * @param array ...$args
     * @return mixed
     */
    public function get($class, ...$args)
    {
        $className = trim($class, '\\');

        /** @var \SplStack|null $pool */
        $pool = $this->map[$className] ?? null;

        // 对象池如果没有初始化
        if ($pool == null) {
            $pool = $this->initNewPool($className);
        }
        // 对象池里没有对象的话创建对象
        if ($pool->count()) {
            $obj = $pool->shift();
            return $obj;
        } else {
            $ref = new \ReflectionClass($class);
            /** @var \EzHttp\Base\Core $obj */
            $obj = $ref->newInstance($args);
            $obj->useCount = 0;
            $obj->genTime = time();
            //销毁无用对象
            unset($ref);

            return $obj;
        }
    }

    /**
     * 将对象回收进对象池
     * @param $classInstance
     */
    public function collect($classInstance)
    {
        $className = trim(get_class($classInstance), '\\');
        $pool = $this->map[$className] ?? null;
        if ($pool == null) {
            $pool = $this->initNewPool($className);
        }
        $pool->push($classInstance);
    }

    /**
     * 创建一个双向链表在对象池里
     * @param $className
     * @return \SplStack
     */
    private function initNewPool($className)
    {
        $this->map[$className] = new \SplStack();

        return $this->map[$className];
    }
}