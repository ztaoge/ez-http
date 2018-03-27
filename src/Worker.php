<?php
namespace EzHttp;

use EzHttp\Http\Http;

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
     * http处理回调
     * @var callable
     */
    public $onMessage;

    public function __construct($host = null)
    {
        if ($host) {
            $this->host = $host;
        }
    }

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
        for ($i = 0; $i < $this->count; $i++) {
            $pid = pcntl_fork();
            if ($pid > 0) {
                //parent worker
            } elseif ($pid == 0) {
                //child worker
                while (1) {
                    $http = new Http();
                    // accept
                    $newSocket = @stream_socket_accept($socket, -1, $remote_address);
                    $buffer = @fread($newSocket, 65536);
                    if ($buffer) {
                        $http->httpDecode($buffer);
                        $http->handle($this->onMessage);
                        $str = $http->response;
                        @fwrite($newSocket, $str, strlen($str));
                    }
                    @fclose($newSocket);
                    unset($http);
                }
            } else {
                throw new \Exception('fork worker fail');
            }
        }
    }

    public function monitorWorker()
    {
        // 回收结束的子进程
        while (1) {
            pcntl_wait($status, WUNTRACED);
        }
    }
}