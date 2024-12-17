<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageQueue\RabbitMq;

use Coffreo\Challenge\MessageQueue\Broker;
use Coffreo\Challenge\MessageQueue\Message;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Exchange\AMQPExchangeType;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * An poorman's RabbitMQ implementation that uses PhpAmqpLib to publish messages.
 */
class AmqpBroker implements Broker
{
    public function __construct(
        private string $host,
        private int $port,
        private string $user,
        private string $password,
        private string $vhost,
    ) {
    }

    public function publish(Message $message): void
    {
        $connection = new AMQPStreamConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vhost,
        );
        $channel = $connection->channel();

        $channel->exchange_declare($message->exchanges, AMQPExchangeType::DIRECT, false, true, false);
        $channel->basic_publish(
            new AMQPMessage(
                $message->payload,
                ['content_type' => 'text/plain', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT],
            ),
            $message->exchanges,
            $message->routingKey,
        );

        $channel->close();
        $connection->close();
    }
}
