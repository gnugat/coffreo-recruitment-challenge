<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageQueue;

/**
 * An abstraction that's going to wrap RabbitMQ.
 */
interface Broker
{
    public function publish(Message $message): void;
}
