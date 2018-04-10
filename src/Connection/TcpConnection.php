<?php

namespace EzHttp\Connection;

use EzHttp\Worker;
use EzHttp\Http\Http;

class TcpConnection
{
    const STATUS_INITIAL = 0;
    const STATUS_CONNECTING = 1;
    const STATUS_ESTABLISHED = 2;
    const STATUS_CLOSING = 4;
    const STATUS_CLOSED = 8;

    /**
     * @var resource
     */
    protected $socket = null;

    /**
     * 连接状态
     * @var int
     */
    protected $status = self::STATUS_ESTABLISHED;

    /**
     * 信息处理
     * @var null|callable
     */
    public $onMessage = null;

    public static $statusMap = [
        self::STATUS_INITIAL => '初始状态',
        self::STATUS_CONNECTING => '连接中',
        self::STATUS_CLOSING => '关闭中',
        self::STATUS_CLOSED => '已关闭',
    ];

    public function __construct($socket)
    {
        $this->socket = $socket;
        stream_set_blocking($socket, 0);
        stream_set_read_buffer($socket, 0);
        Worker::$loop->addReadStream($socket, function ($socket) {
            $this->read($socket);
        });
    }

    /**
     * 读取socket中的信息
     * @param $socket
     */
    public function read($socket)
    {
        $buffer = stream_get_contents($socket);
        if ($buffer === '' || $buffer === false) {
            $this->destroy();
            return;
        }
        try {
            call_user_func($this->onMessage, $this);
        } catch (\Exception $e) {
            echo $e->getMessage() . "\n";
            exit(250);
        }
        return;
    }

    public function write($socket)
    {
    }

    /**
     * 向客户端发送响应信息
     * @param $buffer
     * @return bool
     */
    public function send($buffer)
    {
        if ($this->status === self::STATUS_CLOSED) {
            return false;
        }
        $sendBuffer = HTTP::httpEncode($buffer);
        $len = @fwrite($this->socket, $sendBuffer, strlen($sendBuffer));
        if ($len === false) {
            if (!is_resource($this->socket) || feof($this->socket)) {
                $this->destroy();
            }
        } else {
            @fclose($this->socket);
        }
    }

    /**
     * 销毁eventLoop中的注册的读写fd
     */
    public function destroy()
    {
        if ($this->status == self::STATUS_CLOSED) {
            return;
        }
        Worker::$loop->removeReadStream($this->socket);
        Worker::$loop->removeWriteStream($this->socket);
        @fclose($this->socket);
        $this->status = self::STATUS_CLOSED;
    }
}