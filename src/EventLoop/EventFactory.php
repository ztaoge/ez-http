<?php

namespace EzHttp\EventLoop;


class EventFactory
{
    /**
     * 工厂方式创建一个EventLoop
     * TODO: 当前只支持 IO 复用只有stream_select，以后使用libevent库的epoll等
     */
    public static function createEventLoop()
    {
        return new StreamSelectLoop();
    }
}