<?php

declare(strict_types=1);

namespace Coffreo\Challenge\DependencyInjection\ServiceBuilder;

use Coffreo\Challenge\Amqp\AmqpChannel;
use Coffreo\Challenge\Amqp\AmqpChannelFactory;
use Coffreo\Challenge\DependencyInjection\Container;
use Coffreo\Challenge\MessageHandler\CapitalNamePublished;
use Coffreo\Challenge\MessageHandler\CapitalNamePublished\RetrieveCapitalDataForCapitalName;
use Coffreo\Challenge\MessageHandler\CapitalNamePublishedHandler;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CapitalServiceBuilder
{
    public static function build(Container $container): void
    {
        $container->services[CapitalNamePublishedHandler::class] = new CapitalNamePublishedHandler(
            new RetrieveCapitalDataForCapitalName(
                $container->services[HttpClientInterface::class],
                $container->services[LoggerInterface::class],
            ),
        );
        $container->services['Capital'.AmqpChannel::class] = $container->services[AmqpChannelFactory::class]
            ->make(
                $container->parameters['capital_consumer'],
            );
        $container->services['Capital'.AmqpChannel::class]->register(
            $container->services[CapitalNamePublishedHandler::class],
            'handle',
            CapitalNamePublished::class,
        );
    }
}
