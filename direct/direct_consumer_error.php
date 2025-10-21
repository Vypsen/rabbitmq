<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('direct_logs', 'direct', false, false, false);

[$queue_name, ,] = $channel->queue_declare("", false, false, true, false);

$channel->queue_bind($queue_name, 'direct_logs', 'error');

echo " [*] Error processor waiting for messages. To exit press CTRL+C\n";

$callback = static function ($msg) {
    echo " [x] ERROR: {$msg->body}\n";
    $msg->ack();
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}