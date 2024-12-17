<?php

declare(strict_types=1);

namespace Coffreo\Challenge;

/**
 * App provides access to the Dependency Injection Container (DIC).
 */
class App
{
    public function __construct(
        public Container $container,
    ) {
    }
}
