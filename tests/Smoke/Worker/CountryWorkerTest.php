<?php

declare(strict_types=1);

namespace tests\Coffreo\Challenge\Smoke\Worker;

use Coffreo\Challenge\Amqp\AmqpChannel;
use Coffreo\Challenge\MessageHandler\CountryCodePublished;
use PHPUnit\Framework\TestCase;
use tests\Coffreo\Challenge\AppSingleton;

/**
 * Smoke test dedicated to the `country` worker.
 */
class CountryWorkerTest extends TestCase
{
    public function testItConsumesValidCountryNames(): void
    {
        $app = AppSingleton::get();
        $countryAmqpChannel = $app->container->get('Country'.AmqpChannel::class);

        // cca2, cca3, ccn3 and cioc
        $payloads = ['DE', 'ES', 'FR', 'USA', '170'];
        foreach ($payloads as $payload) {
            $countryAmqpChannel->publish(
                new CountryCodePublished($payload),
            );
        }

        // Wait 2 second per message to process (req/s is very low: 0.8)
        sleep(\count($payloads) * 2);

        // Absence of failure is enough to validate smoke tests
        $this->expectNotToPerformAssertions();
    }

    public function testItIgnoresInvalidCountryNames(): void
    {
        $app = AppSingleton::get();
        $countryAmqpChannel = $app->container->get('Country'.AmqpChannel::class);

        $payloads = ['D', 'FRANCE', '1337', '', "\n", '$'];
        foreach ($payloads as $payload) {
            $countryAmqpChannel->publish(
                new CountryCodePublished($payload),
            );
        }

        // Wait 2 second per message to process (req/s is very low: 0.8)
        sleep(\count($payloads) * 2);

        // Absence of failure is enough to validate smoke tests
        $this->expectNotToPerformAssertions();
    }

    public function testItPicksUpTheSlack(): void
    {
        $app = AppSingleton::get();
        $countryAmqpChannel = $app->container->get('Country'.AmqpChannel::class);
        $countryWorker = $app->container->get(CountryWorker::class);

        // Stopping the worker
        // $countryWorker->run();

        // Publishing messages, with no active consumers
        $payloads = ['EGY', 'JP', 'bra'];
        foreach ($payloads as $payload) {
            $countryAmqpChannel->publish(
                new CountryCodePublished($payload),
            );
        }

        // Restarting the worker
        $countryWorker->run();

        // Wait 2 second per message to process (req/s is very low: 0.8)
        sleep(\count($payloads) * 2);

        // Absence of failure is enough to validate smoke tests
        $this->expectNotToPerformAssertions();
    }
}
