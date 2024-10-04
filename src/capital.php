<?php

// AMQP consumer

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

$exchange = 'router';
$queue = 'capital';
$consumerTag = 'consumer';

$connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest', '/');

/**
 * @var \PhpAmqpLib\Channel\AMQPChannel $channel
 */
$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);

$channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

$channel->queue_bind($queue, $exchange, 'capital');

/**
 * @param \PhpAmqpLib\Message\AMQPMessage $message
 */
function process_message($message)
{
    echo "\n--------\n";
    echo $message->body;
    echo "\n--------\n";

    // Call API by curl to get country information
    $url = sprintf('https://restcountries.com/v3.1/capital/%s', $message->body);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    $json = json_decode($response, true);

    echo var_export($json, true). PHP_EOL;

    $message->ack();
}

$channel->basic_consume($queue, $consumerTag, false, false, false, false, 'process_message');

/**
 * @param \PhpAmqpLib\Channel\AMQPChannel $channel
 * @param \PhpAmqpLib\Connection\AbstractConnection $connection
 */
function shutdown($channel, $connection)
{
    $channel->close();
    $connection->close();
}

register_shutdown_function('shutdown', $channel, $connection);

// Loop as long as the channel has callbacks registered
$channel->consume();