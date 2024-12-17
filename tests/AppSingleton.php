<?php

declare(strict_types=1);

namespace tests\Coffreo\Challenge;

use Coffreo\Challenge\App;
use Coffreo\Challenge\Container;

/**
 * Provides App for the tests, as a singleton for performance reasons:
 * This allows the Dependency Injection Container to be built only once during
 * the whole testsuite.
 */
class AppSingleton
{
    private static ?App $app = null;

    public static function get(): App
    {
        if (null !== self::$app) {
            return self::$app;
        }
        self::$app = new App(
            new Container(),
        );
        self::$app->container->build();

        return self::$app;
    }
}
