<?php

namespace EzHttp\EventLoop;

class StreamSelectLoop implements LoopInterface
{
    private $readStreams = [];
    private $readListeners = [];
    private $writeStreams = [];
    private $writeListeners = [];

    public function addReadStream($stream, callable $listener)
    {
        $key = (int) $stream;
        if (!isset($this->readStreams[$key])) {
            $this->readStreams[$key] = $stream;
            $this->readListeners[$key] = $listener;
        }
    }

    public function addWriteStream($stream, callable $listener)
    {
        $key = (int) $stream;
        if (!isset($this->writeStreams[$key])) {
            $this->writeStreams[$key] = $stream;
            $this->writeListeners[$key] = $listener;
        }
    }

    public function removeReadStream($stream)
    {
        $key = (int) $stream;
        unset($this->readStreams[$key], $this->readListeners[$key]);
    }

    public function removeWriteStream($stream)
    {
        $key = (int) $stream;
        unset($this->writeStreams[$key], $this->writeListeners[$key]);
    }

    /**
     * 不断地调用select
     * event loop
     */
    public function loop()
    {
        while (1) {
            $this->waitForStreamActivity(null);
        }
    }

    /**
     * 调用select，处理已准备好的读或写操作
     * @param int $timeout
     */
    private function waitForStreamActivity($timeout)
    {
        $read = $this->readStreams;
        $write = $this->writeStreams;

        $availableStreamNum = $this->streamSelect($read, $write, $timeout);
        if (false === $availableStreamNum) {
            return;
        }

        foreach ($read as $stream) {
            $key = (int) $stream;
            if (isset($this->readListeners[$key])) {
                call_user_func($this->readListeners[$key], $stream);
            }
        }

        foreach ($write as $stream) {
            $key = (int) $stream;
            if (isset($this->writeListeners[$key])) {
                call_user_func($this->writeListeners[$key], $stream);
            }
        }
    }

    /**
     * stream select
     * @param $read
     * @param $write
     * @param $timeout
     * @return int
     */
    protected function streamSelect(&$read, &$write, $timeout)
    {
        if ($read || $write) {
            $except = null;

            // suppress warnings that occur, when stream_select is interrupted by a signal
            // stream_select timeout时间不能都设置为0，这样每次stream_select调用都有返回，会增加脚本的cpu时间
            return @stream_select($read, $write, $except, $timeout === null ? null : 0, $timeout);
        }
        $timeout && usleep($timeout);
    }
}