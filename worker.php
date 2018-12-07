<?php
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->queue_declare('task_queue', false, true, false, false);
echo " [*] Waiting for messages. To exit press CTRL+C\n";
$callback = function ($msg) {
    echo ' [x] Received ', $msg->body, "\n";
    sleep(substr_count($msg->body, '.'));
    echo " [x] Done\n";
  //
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};
//公平调度,basic_qos 第二个参数 prefetch_count = 1
$channel->basic_qos(null, 1, null);
//开启消息确认机制,第四个参数fasle
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);
while (count($channel->callbacks)) {
    $channel->wait();
}
$channel->close();
$connection->close();