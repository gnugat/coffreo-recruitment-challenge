<?php

// AMQP consumer

require_once __DIR__ . '/../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

$exchange = 'router';
$queue = 'country';
$consumerTag = 'consumer';

$connection = new AMQPStreamConnection('rabbitmq', 5672, 'guest', 'guest', '/');

/**
 * @var \PhpAmqpLib\Channel\AMQPChannel $channel
 */
$channel = $connection->channel();

$channel->queue_declare($queue, false, true, false, false);

$channel->exchange_declare($exchange, AMQPExchangeType::DIRECT, false, true, false);

$channel->queue_bind($queue, $exchange, 'country');

/**
 * @param \PhpAmqpLib\Message\AMQPMessage $message
 */
function process_message($message)
{
    global $exchange;

    echo "\n--------\n";
    echo $message->body;
    echo "\n--------\n";

    // Call API by curl to get country information
    $url = sprintf('https://restcountries.com/v3.1/alpha/%s', $message->body);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);

    $json = json_decode($response, true);

    $capital = $json[0]['capital'][0];

    // Publish the capital to the exchange
    $publishMessage = new AMQPMessage(
        $capital,
        array('content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT)
    );

    $message->getChannel()->basic_publish($publishMessage, $exchange, 'capital');

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