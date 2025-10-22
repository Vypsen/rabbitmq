<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Wire\AMQPTable;

$connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
$channel = $connection->channel();

$channel->queue_declare('my_dlq', false, true, false, false);

$args = new AMQPTable([
    'x-dead-letter-exchange' => '',
    'x-dead-letter-routing-key' => 'my_dlq'
]);

$channel->queue_declare('main_queue', false, true, false, false, false, $args);