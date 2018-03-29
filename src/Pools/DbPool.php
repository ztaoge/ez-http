<?php

namespace EzHttp\Pools;

interface DbPool
{
    /**
     * 检查连接（连接句柄可能会超时）
     * @return mixed
     */
    public function check();
}