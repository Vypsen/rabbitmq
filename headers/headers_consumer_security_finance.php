<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('headers_data', 'headers', false, false, false);

[$queue_name, ,] = $channel->queue_declare("", false, false, true, false);

$binding_args = new AMQPTable([
    'department' => 'security',
    'department' => 'finance',
    'x-match' => 'any'
]);

$channel->queue_bind($queue_name, 'headers_data', '', false, $binding_args);

echo " [*] Security/Finance monitor waiting for messages\n";

$callback = static function ($msg) {
    $headers = $msg->has('application_headers')
        ? $msg->get('application_headers')->getNativeData()
        : [];

    $department = $headers['department'] ?? 'unknown';
    echo " [{$department}] {$msg->body}\n";
    $msg->ack();
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}