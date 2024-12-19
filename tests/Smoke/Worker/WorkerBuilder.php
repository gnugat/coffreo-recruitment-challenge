<?php

declare(strict_types=1);

namespace tests\Coffreo\Challenge\Smoke\Worker;

use Coffreo\Challenge\DependencyInjection\Container;

class WorkerBuilder
{
    public static function build(Container $container): void
    {
        $container->services[CapitalWorker::class] = new CapitalWorker();
        $container->services[CountryWorker::class] = new CountryWorker();
    }
}
