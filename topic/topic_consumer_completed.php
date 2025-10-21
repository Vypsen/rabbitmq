<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('topic_events', 'topic', false, false, false);

[$queue_name, ,] = $channel->queue_declare("", false, false, true, false);

$channel->queue_bind($queue_name, 'topic_events', '*.completed');

echo " [*] Analytics service waiting for *.completed events\n";

$callback = function ($msg) {
    $routingKey = $msg->get('routing_key');
    echo " [analytics] {$routingKey}: {$msg->body}\n";
    $msg->ack();
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}