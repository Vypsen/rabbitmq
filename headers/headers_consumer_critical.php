<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('headers_data', 'headers', false, false, false);

[$queue_name, ,] = $channel->queue_declare("", false, false, true, false);

$binding_args = new AMQPTable([
    'priority' => 'high',
    'environment' => 'production',
    'x-match' => 'all'
]);

$channel->queue_bind($queue_name, 'headers_data', '', false, $binding_args);

echo " [*] Critical alerts service waiting for high priority production messages\n";

$callback = static function ($msg) {
    $headers = $msg->has('application_headers')
        ? $msg->get('application_headers')->getNativeData()
        : [];

    echo " [CRITICAL] {$msg->body}\n";
    echo "     Headers: " . json_encode($headers, JSON_THROW_ON_ERROR) . "\n";
    $msg->ack();
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}