<?php

declare(strict_types=1);

use PhpAmqpLib\Exchange\AMQPExchangeType;

return [
    'capital_consumer' => [
        'exchange_options' => [
            'exchange' => 'router',
            'type' => AMQPExchangeType::DIRECT,
        ],
        'routing_options' => [
            'queue' => 'capital',
            'routing_key' => 'capital',
        ],
        'tag' => 'consumer',
    ],
];
