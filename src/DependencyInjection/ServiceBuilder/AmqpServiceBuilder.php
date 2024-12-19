<?php

declare(strict_types=1);

namespace Coffreo\Challenge\DependencyInjection\ServiceBuilder;

use Coffreo\Challenge\Amqp\AmqpChannelFactory;
use Coffreo\Challenge\Amqp\AmqpConnection;
use Coffreo\Challenge\DependencyInjection\Container;

class AmqpServiceBuilder
{
    public static function build(Container $container): void
    {
        $container->parameters = array_merge(
            $container->parameters,
            require __DIR__.'/../../../config/capital_consumer.php',
            require __DIR__.'/../../../config/country_consumer.php',
        );
        $container->services[AmqpConnection::class] = AmqpConnection::fromDsn(
            $_ENV['RABBITMQ_URL'] ?? '',
        );
        $container->services[AmqpChannelFactory::class] = new AmqpChannelFactory(
            $container->services[AmqpConnection::class],
        );
    }
}
