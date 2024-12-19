<?php

declare(strict_types=1);

namespace Coffreo\Challenge\DependencyInjection\ServiceBuilder;

use Coffreo\Challenge\Amqp\AmqpChannel;
use Coffreo\Challenge\Amqp\AmqpChannelFactory;
use Coffreo\Challenge\DependencyInjection\Container;
use Coffreo\Challenge\MessageHandler\CountryCodePublished;
use Coffreo\Challenge\MessageHandler\CountryCodePublished\PublishCapitalName;
use Coffreo\Challenge\MessageHandler\CountryCodePublished\RetrieveCapitalNameForCountryCode;
use Coffreo\Challenge\MessageHandler\CountryCodePublishedHandler;
use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class CountryServiceBuilder
{
    public static function build(Container $container): void
    {
        $container->services[CountryCodePublishedHandler::class] = new CountryCodePublishedHandler(
            new PublishCapitalName(
                $container->services['Capital'.AmqpChannel::class],
            ),
            new RetrieveCapitalNameForCountryCode(
                $container->services[HttpClientInterface::class],
                $container->services[LoggerInterface::class],
            ),
        );
        $container->services['Country'.AmqpChannel::class] = $container->services[AmqpChannelFactory::class]
            ->make(
                $container->parameters['country_consumer'],
            );
        $container->services['Country'.AmqpChannel::class]->register(
            $container->services[CountryCodePublishedHandler::class],
            'handle',
            CountryCodePublished::class,
        );
    }
}
