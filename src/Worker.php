<?php
namespace EzHttp;

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
    public $loop;

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
        $this->loop = EventLoopFactory::createEventLoop();
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
        stream_set_blocking($socket, 0);
        $this->mainSocket = $socket;

        // start forking worker
        for ($i = 0; $i < $this->count; $i++) {
            $pid = pcntl_fork();
            if ($pid > 0) {
            } elseif ($pid == 0) {
                $this->acceptConnection($socket);
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
     * accept socket连接
     * @param $socket
     */
    public function acceptConnection($socket)
    {
        $this->loop->addReadStream($socket, function ($socket) {
            $newSocket = @stream_socket_accept($socket);
            if ($newSocket) {
                stream_set_read_buffer($newSocket, 0);
                $buffer = stream_get_contents($newSocket);
                if ($buffer) {
                    echo $buffer;
                    $requestParams = Http::httpDecode($buffer);
                    $msg = json_encode($requestParams);
                    $response = Http::httpEncode($msg);
                    @fwrite($newSocket, $response, strlen($response));
                }
            }
            @fclose($newSocket);
        });
        $this->loop->loop();
    }

}