<?php

use EzHttp\Http\Request;

require_once 'vendor/autoload.php';

$worker = new \EzHttp\Worker('0.0.0.0:8233');
$worker->onMessage = function () {
    return 'hello world';
};
//$worker->run();


$pool = \EzHttp\Base\Pool::getInstance();
for ($i = 0; $i < 10; $i++) {
    /** @var Request $request */
    $request = $pool->get(Request::class);
    // 标记加1
    $request->useCount++;
    // 销毁当前对象属性
    $request->destroy();
    // 回收对象
    $pool->push($request);
}

