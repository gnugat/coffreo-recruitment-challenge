<?php

declare(strict_types=1);

namespace Coffreo\Challenge\Amqp;

class AmqpChannelFactory
{
    public function __construct(
        private AmqpConnection $amqpConnection,
    ) {
    }

    public function make(array $consumerConfig): AmqpChannel
    {
        $channel = $this->amqpConnection->channel();
        $channel->queue_declare(
            queue: $consumerConfig['routing_options']['queue'],
            durable: true,
            auto_delete: false,
        );
        $channel->exchange_declare(
            exchange: $consumerConfig['exchange_options']['exchange'],
            type: $consumerConfig['exchange_options']['type'],
            durable: true,
            auto_delete: false,
        );
        $channel->queue_bind(
            queue: $consumerConfig['routing_options']['queue'],
            exchange: $consumerConfig['exchange_options']['exchange'],
            routing_key: $consumerConfig['routing_options']['routing_key'],
        );

        return new AmqpChannel($channel, $consumerConfig);
    }
}
