<?php

namespace EzHttp\Base;

class StreamSelectLoop
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

    /**
     * event loop
     */
    public function loop()
    {
        while (1) {
            $this->waitForStreamActivity($timeout = 0);
        }
    }

    private function waitForStreamActivity($timeout)
    {
        $read = $this->readStreams;
        $write = $this->writeStreams;

        $availableStreamNum = @stream_select($read, $write, $except, $timeout);
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
}