<?php
/**
 * Created by PhpStorm.
 * User: windows
 * Date: 2018/12/5
 * Time: 16:46
 */
require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
//创建连接
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
//创建信道
$channel = $connection->channel();
//声明队列
$channel->queue_declare('hello', false, false, false, false);
$msg = new AMQPMessage('Hello World!');
//发送消息
$channel->basic_publish($msg, '', 'hello');
echo " [x] Sent 'Hello World!'\n";
$channel->close();
$connection->close();
