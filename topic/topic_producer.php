<?php

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();


$channel->exchange_declare('topic_events', 'topic', false, false, false);

$events = [
    ['key' => 'user.registration.completed', 'body' => 'User john registered'],
    ['key' => 'user.profile.updated', 'body' => 'Profile updated for user123'],
    ['key' => 'order.payment.completed', 'body' => 'Payment received for order #456'],
    ['key' => 'order.shipping.delayed', 'body' => 'Shipping delayed for order #789'],
    ['key' => 'system.backup.completed', 'body' => 'Nightly backup completed'],
];

foreach ($events as $event) {
    $msg = new AMQPMessage($event['body']);
    $channel->basic_publish($msg, 'topic_events', $event['key']);
    echo " [x] Sent '{$event['body']}' with key '{$event['key']}'\n";
}

$channel->close();
$connection->close();