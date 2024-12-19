<?php

declare(strict_types=1);

use PhpAmqpLib\Exchange\AMQPExchangeType;

return [
    'country_consumer' => [
        'exchange_options' => [
            'exchange' => 'router',
            'type' => AMQPExchangeType::DIRECT,
        ],
        'routing_options' => [
            'queue' => 'country',
            'routing_key' => 'country',
        ],
        'tag' => 'consumer',
    ],
];
