<?php

declare(strict_types=1);

namespace Coffreo\Challenge;

use Symfony\Component\Dotenv\Dotenv;

/**
 * Dependency Injection Container.
 */
class Container
{
    public function __construct(
        private array $services = [],
    ) {
    }

    public function build(): void
    {
        // loads .env, .env.local, and .env.$APP_ENV.local or .env.$APP_ENV
        (new Dotenv())->loadEnv(__DIR__.'/../.env');

        $this->services[Worker\CapitalWorker::class] = new Worker\CapitalWorker();
        $this->services[Worker\CountryWorker::class] = new Worker\CountryWorker();
        $this->services[MessageQueue\Broker::class] = MessageQueue\RabbitMq\AmqpBrokerFactory::fromDsn(
            $_ENV['RABBITMQ_URL'] ?? '',
        );
    }

    public function get(string $id): mixed
    {
        $service = $this->services[$id] ?? null;
        if (null === $service) {
            throw new \InvalidArgumentException("DIC: couldn't find service with ID \"{$id}\"");
        }

        return $service;
    }
}
