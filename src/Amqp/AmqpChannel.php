<?php

declare(strict_types=1);

namespace Coffreo\Challenge\Amqp;

use PhpAmqpLib\Channel\AbstractChannel;
use PhpAmqpLib\Message\AMQPMessage;

class AmqpChannel
{
    public function __construct(
        private AbstractChannel $amqpChannel,
        private array $consumerConfig,
        private array $handlers = [],
    ) {
    }

    public function register(mixed $handler, string $method, string $message): void
    {
        $this->handlers[] = [
            'service' => $handler,
            'method' => $method,
            'message' => $message,
        ];
    }

    public function consume(): void
    {
        $handlers = $this->handlers;

        $this->amqpChannel->basic_consume(
            queue: $this->consumerConfig['routing_options']['queue'],
            consumer_tag: $this->consumerConfig['tag'],
            callback: function (AMQPMessage $amqpMessage) use ($handlers) {
                foreach ($handlers as $handler) {
                    $handler['service']->{$handler['method']}(
                        new $handler['message']($amqpMessage->body)
                    );
                }

                $amqpMessage->ack();
            }
        );
        $this->amqpChannel->consume();
    }

    public function publish(mixed $message): void
    {
        $amqpMessage = new AMQPMessage(
            body: (string) $message,
            properties: [
                'content_type' => 'text/plain',
                'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
            ],
        );

        $this->amqpChannel->basic_publish(
            msg: $amqpMessage,
            exchange: $this->consumerConfig['exchange_options']['exchange'],
            routing_key: $this->consumerConfig['routing_options']['routing_key'],
        );
    }
}
