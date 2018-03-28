<?php

namespace EzHttp\Pools;

class MysqlPool implements DbPool
{
    /**
     * @var
     */
    public $pool;

    public function check()
    {
        // TODO: Implement check() method.
    }

    public function push($client)
    {
        $this->pool->push($client);

        return $this;
    }
}