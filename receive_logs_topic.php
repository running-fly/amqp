<?php
//php receive_logs_topic.php   one   a.*
//php receive_logs_topic.php  two  a.*
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();
$channel->exchange_declare('topic_logs', 'topic', false, true, false);
list($queue_name) = array_slice($argv, 1,1);
if (empty($queue_name)) {
    file_put_contents('php://stderr', "Usage: $argv[0] [queue_name]\n");
    exit(1);
}
//第四个参数：消费者是否唯一
$channel->queue_declare($queue_name, false, true, false, false);
$binding_keys = array_slice($argv, 2);
if (empty($binding_keys)) {
    file_put_contents('php://stderr', "Usage: $argv[0] [binding_key]\n");
    exit(1);
}
foreach ($binding_keys as $binding_key) {
    $channel->queue_bind($queue_name, 'topic_logs', $binding_key);
}
echo " [*] Waiting for logs. To exit press CTRL+C\n";
$callback = function ($msg) {
    sleep(3);
    $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
    echo ' [x] ', $msg->delivery_info['routing_key'], ':', $msg->body, "\n";

};
$channel->basic_consume($queue_name, '', false, false, false, false, $callback);
while (count($channel->callbacks)) {
    $channel->wait();
}
$channel->close();
$connection->close();