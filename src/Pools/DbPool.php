<?php

namespace EzHttp\Pools;

interface DbPool
{
    /**
     * 检查链接
     * @return mixed
     */
    public function check();
}