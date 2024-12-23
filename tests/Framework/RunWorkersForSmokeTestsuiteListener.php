<?php

declare(strict_types=1);

namespace tests\Coffreo\Challenge\Framework;

use PHPUnit\Framework\TestListener;
use PHPUnit\Framework\TestListenerDefaultImplementation;
use PHPUnit\Framework\TestSuite;
use tests\Coffreo\Challenge\AppSingleton;
use tests\Coffreo\Challenge\Smoke\Worker\CapitalWorker;
use tests\Coffreo\Challenge\Smoke\Worker\CountryWorker;
use tests\Coffreo\Challenge\Smoke\Worker\WorkerBuilder;

/**
 * Makes sure the workers are running for the smoke testsuite,
 * and to stop them when the testsuite is finished.
 */
class RunWorkersForSmokeTestsuiteListener implements TestListener
{
    // Provides empty stubs for all TestListener methods,
    // so we can implement only the ones we need.
    use TestListenerDefaultImplementation;

    public function __construct(
        private string $smokeTestsuiteName,
    ) {
    }

    public function startTestSuite(TestSuite $suite): void
    {
        if ($this->smokeTestsuiteName !== $suite->getName()) {
            return;
        }
        $app = AppSingleton::get();
        WorkerBuilder::build($app->container);
        $workers = [];
        $workers[] = $app->container->get(CapitalWorker::class);
        $workers[] = $app->container->get(CountryWorker::class);
        foreach ($workers as $worker) {
            $worker->run();
        }
    }

    public function endTestSuite(TestSuite $suite): void
    {
        if ($this->smokeTestsuiteName !== $suite->getName()) {
            return;
        }
        $app = AppSingleton::get();
        $workers = [];
        $workers[] = $app->container->get(CapitalWorker::class);
        $workers[] = $app->container->get(CountryWorker::class);
        foreach ($workers as $worker) {
            $worker->stop();
        }
    }
}
