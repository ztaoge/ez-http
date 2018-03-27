<?php

namespace EzHttp\Base;

class Core
{
    /**
     * @var int 创建时间
     */
    public $genTime = null;

    /**
     * @var int 使用计数
     */
    public $useCount = 0;

    /**
     * 对象是否被销毁
     * @var bool
     */
    public $isDestroy = false;

    /**
     * 对象回收方法
     */
    public function destroy()
    {
        if (!$this->isDestroy) {
            $this->isDestroy = true;
        }
    }
}