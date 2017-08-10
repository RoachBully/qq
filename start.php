<?php
use Workerman\Worker;
require_once __DIR__ . '/workerman/Autoloader.php';
$global_uid = 0;

// 当客户端连上来时分配uid，并保存连接，并通知所有客户端
function handle_connection($connection)
{
    global $http_worker, $global_uid;
    // 为这个链接分配一个uid
    $connection->uid = ++$global_uid;
}

// 当客户端发送消息过来时，转发给所有人
function handle_message($connection, $str)
{
    $str = '测试123';
    global $http_worker;
    foreach($http_worker->connections as $conn)
    {
        $conn->send("user[{$connection->uid}] said: $str");
    }
}

// 当客户端断开时，广播给所有客户端
function handle_close($connection)
{
    global $http_worker;
    foreach($http_worker->connections as $conn)
    {
        $conn->send("user[{$connection->uid}] logout");
    }
}

// 创建一个文本协议的Worker监听2347接口
$http_worker = new Worker("http://127.0.0.1:1234");

// 只启动1个进程，这样方便客户端之间传输数据
$http_worker->count = 4;

$http_worker->onConnect = 'handle_connection';
$http_worker->onMessage = 'handle_message';
$http_worker->onClose = 'handle_close';

Worker::runAll();
