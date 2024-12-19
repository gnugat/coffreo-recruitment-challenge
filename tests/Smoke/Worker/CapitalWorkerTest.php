<?php

declare(strict_types=1);

namespace tests\Coffreo\Challenge\Smoke\Worker;

use Coffreo\Challenge\Amqp\AmqpChannel;
use Coffreo\Challenge\MessageHandler\CapitalNamePublished;
use PHPUnit\Framework\TestCase;
use tests\Coffreo\Challenge\AppSingleton;

/**
 * Smoke test dedicated to the `capital` worker.
 */
class CapitalWorkerTest extends TestCase
{
    public function testItConsumesValidCapitalNames(): void
    {
        $app = AppSingleton::get();
        $capitalAmqpChannel = $app->container->get('Capital'.AmqpChannel::class);

        $payloads = ['London', 'Paris', 'Wahsington D.C.'];
        foreach ($payloads as $payload) {
            $capitalAmqpChannel->publish(
                new CapitalNamePublished($payload),
            );
        }

        // Wait 2 second per message to process (req/s is very low: 0.8)
        sleep(\count($payloads) * 2);

        // Absence of failure is enough to validate smoke tests
        $this->expectNotToPerformAssertions();
    }

    public function testItIgnoresInvalidCapitalNames(): void
    {
        $app = AppSingleton::get();
        $capitalAmqpChannel = $app->container->get('Capital'.AmqpChannel::class);

        $payloads = ['FR', 'United Kingdom', 'Kingston-Upon-Thames', '42', '', "\n", '$'];
        foreach ($payloads as $payload) {
            $capitalAmqpChannel->publish(
                new CapitalNamePublished($payload),
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
        $capitalAmqpChannel = $app->container->get('Capital'.AmqpChannel::class);
        $capitalWorker = $app->container->get(CapitalWorker::class);

        // Stopping the worker
        $capitalWorker->stop();

        // Publishing messages, with no active consumers
        $payloads = ['Cairo', 'Tokyo', 'Brasília'];
        foreach ($payloads as $payload) {
            $capitalAmqpChannel->publish(
                new CapitalNamePublished($payload),
            );
        }

        // Restarting the worker
        $capitalWorker->run();

        // Wait 2 second per message to process (req/s is very low: 0.8)
        sleep(\count($payloads) * 2);

        // Absence of failure is enough to validate smoke tests
        $this->expectNotToPerformAssertions();
    }
}
