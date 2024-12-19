<?php

declare(strict_types=1);

namespace Coffreo\Challenge\DependencyInjection\ServiceBuilder;

use Coffreo\Challenge\DependencyInjection\Container;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

class LoggerServiceBuilder
{
    public static function build(Container $container): void
    {
        $container->services[LoggerInterface::class] = new Logger('challenge');
        $container->services[LoggerInterface::class]->pushHandler(new StreamHandler(
            'php://stderr',
            LogLevel::DEBUG,
        ));
    }
}
