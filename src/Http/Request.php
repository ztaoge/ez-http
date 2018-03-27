<?php

namespace EzHttp\Http;

use EzHttp\Base\Core;

class Request extends Core
{
    public $id;

    public $get;

    public $post;

    public $clientIp;

    public function __construct()
    {
    }

    /**
     * 销毁对象相关属性
     */
    public function destroy()
    {
        parent::destroy();
        $this->id = null;
        $this->get = null;
        $this->post = null;
        $this->clientIp = null;
    }
}