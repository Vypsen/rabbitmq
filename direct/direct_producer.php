<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->exchange_declare('direct_logs', 'direct', false, false, false);

$messages = [
    ['routing_key' => 'error', 'body' => 'Critical error in payment service'],
    ['routing_key' => 'warning', 'body' => 'High memory usage detected'],
    ['routing_key' => 'info', 'body' => 'User login successful'],
    ['routing_key' => 'error', 'body' => 'Database connection failed'],
];

foreach ($messages as $message) {
    $msg = new AMQPMessage($message['body']);
    $channel->basic_publish($msg, 'direct_logs', $message['routing_key']);
    echo " [x] Sent '{$message['body']}' with key '{$message['routing_key']}'\n";
}

$channel->close();
$connection->close();