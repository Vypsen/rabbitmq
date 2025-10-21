<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();


$channel->exchange_declare('headers_data', 'headers', false, false, false);

$messages = [
    [
        'body' => 'Critical security alert',
        'headers' => ['priority' => 'high', 'department' => 'security', 'environment' => 'production']
    ],
    [
        'body' => 'Regular analytics report',
        'headers' => ['priority' => 'low', 'department' => 'analytics', 'format' => 'json']
    ],
    [
        'body' => 'User payment processed',
        'headers' => ['priority' => 'medium', 'department' => 'finance', 'environment' => 'production']
    ],
    [
        'body' => 'Development server backup',
        'headers' => ['priority' => 'low', 'department' => 'operations', 'environment' => 'development']
    ]
];

foreach ($messages as $message) {
    $msg = new AMQPMessage($message['body']);

    $headers = new AMQPTable($message['headers']);
    $msg->set('application_headers', $headers);

    $channel->basic_publish($msg, 'headers_data', '');

    echo " [x] Sent '{$message['body']}' with headers: " . json_encode($message['headers'], JSON_THROW_ON_ERROR) . "\n";
}

$channel->close();
$connection->close();