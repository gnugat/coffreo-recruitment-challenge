<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageQueue;

class Message
{
    public function __construct(
        public string $routingKey,
        public string $payload,
        public string $exchanges,
        public array $properties = [],
        public string $payloadEncoding = 'string',
    ) {
    }
}
