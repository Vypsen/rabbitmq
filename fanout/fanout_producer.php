<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();


$channel->exchange_declare('notifications', 'fanout', false, false, false);

$notifications = [
    'System maintenance scheduled at 3:00 AM',
    'New user registered: john@example.com',
    'Payment received: $150.00',
    'Database backup completed successfully'
];

foreach ($notifications as $notification) {
    $msg = new AMQPMessage($notification);

    $channel->basic_publish($msg, 'notifications', '');
    echo " [x] Broadcast: {$notification}\n";
}

$channel->close();
$connection->close();