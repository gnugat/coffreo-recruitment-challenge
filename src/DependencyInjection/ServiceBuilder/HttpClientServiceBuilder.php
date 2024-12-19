<?php

declare(strict_types=1);

namespace Coffreo\Challenge\DependencyInjection\ServiceBuilder;

use Coffreo\Challenge\DependencyInjection\Container;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class HttpClientServiceBuilder
{
    public static function build(Container $container): void
    {
        $container->services[HttpClientInterface::class] = new RetryableHttpClient(
            HttpClient::create()
        );
    }
}
