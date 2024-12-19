<?php

declare(strict_types=1);

namespace Coffreo\Challenge;

use Coffreo\Challenge\DependencyInjection\Container;
use Symfony\Component\Dotenv\Dotenv;

/**
 * App provides access to a configured Dependency Injection Container (DIC).
 */
class App
{
    /**
     * If environment variables APP_ENV and APP_DEBUG are not set, use these default values.
     */
    public function __construct(
        public Container $container,
        public string $defaultEnv = 'dev',
        public bool $defaultDebug = true,
    ) {
    }

    public function build(): void
    {
        if (!\array_key_exists('APP_ENV', $_ENV)) {
            $_ENV['APP_ENV'] = $this->defaultEnv;
        }
        if (!\array_key_exists('APP_DEBUG', $_ENV)) {
            $_ENV['APP_DEBUG'] = $this->defaultDebug;
        }

        // loads .env, .env.local, and .env.$APP_ENV.local or .env.$APP_ENV
        (new Dotenv())->loadEnv(
            __DIR__.'/../.env',
            defaultEnv: $this->defaultEnv,
        );

        $this->container->build();
    }
}
