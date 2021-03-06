<?php
namespace EzHttp;

use EzHttp\Connection\TcpConnection;
use EzHttp\EventLoop\EventLoopFactory;
use EzHttp\Http\Http;
use EzHttp\Base\Pool;
use EzHttp\Http\Request;
use EzHttp\EventLoop\LoopInterface;

Class Worker
{
    public $mainSocket;

    /**
     * fork worker num
     * @var int
     */
    public $count = 1;

    /**
     * @var null|string
     */
    public $host = '0.0.0.0:80';

    /**
     * @var LoopInterface
     */
    public static $loop;

    /**
     * http处理回调
     * @var callable
     */
    public $onMessage;

    public function __construct($host = null)
    {
        if ($host) {
            $this->host = $host;
        }
        //注册全局事件轮询变量
        self::$loop = EventLoopFactory::createEventLoop();
    }

    /**
     * 运行worker
     * @throws \Exception
     */
    public function run()
    {
        // fork worker
        $this->forkWorker();
        // monitor worker
        $this->monitorWorker();
    }

    /**
     * TODO: 频繁的实例化，销毁对象，性能是否有问题？可能会出现OOM问题？能否引入对象池？
     * fork worker
     * @throws \Exception
     */
    public function forkWorker()
    {
        $socket = stream_socket_server("tcp://$this->host", $errno, $errstr);
        echo "listen on http://$this->host\n";
        $this->mainSocket = $socket;

        // start forking worker
        // TODO: 如果使用epoll等，多进程accept是否会出现惊群？
        for ($i = 0; $i < $this->count; $i++) {
            $pid = pcntl_fork();
            if ($pid > 0) {
            } elseif ($pid == 0) {
                stream_set_blocking($socket, 0);
                $this->acceptConnection();
            } else {
                throw new \Exception('fork worker fail');
            }
        }
    }

    /**
     * 监控各个子进程
     */
    public function monitorWorker()
    {
        // 回收结束的子进程
        while (1) {
            pcntl_wait($status, WUNTRACED);
        }
    }

    /**
     * accept socket连接 非阻塞
     */
    public function acceptConnection()
    {
        self::$loop->addReadStream($this->mainSocket, function ($socket) {
            // 设置accept的超时时间为0，防止请求超时进程被阻塞
            $newSocket = @stream_socket_accept($socket, 0);
            // 连接失败
            if (!$newSocket) {
                return;
            }
            $conn = new TcpConnection($newSocket);
            $conn->onMessage = $this->onMessage;
        });
        self::$loop->loop();
    }

}