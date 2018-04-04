<?php

namespace EzHttp\EventLoop;

interface LoopInterface
{
    /**
     * 为可读stream资源注册listener
     * @param $stream
     * @param callable $listener
     */
    public function addReadStream($stream, callable $listener);

    /**
     * 为可写stream资源注册listener
     * @param $stream
     * @param callable $listener
     */
    public function addWriteStream($stream, callable $listener);

    /**
     * 解除可读stream资源listener的绑定
     * @param $stream
     */
    public function removeReadStream($stream);

    /**
     * 解除可写stream资源listener的绑定
     * @param $stream
     */
    public function removeWriteStream($stream);

    /**
     * 执行事件 loop
     */
    public function loop();
}