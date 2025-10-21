<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('direct_logs', 'direct', false, false, false);

[$queue_name, ,] = $channel->queue_declare("", false, false, true, false);


$severities = ['error', 'warning', 'info'];
foreach ($severities as $severity) {
    $channel->queue_bind($queue_name, 'direct_logs', $severity);
}

echo " [*] Log aggregator waiting for all messages\n";

$callback = static function ($msg) {
    $severity = $msg->get('routing_key');
    echo " [{$severity}] {$msg->body}\n";
    $msg->ack();
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}