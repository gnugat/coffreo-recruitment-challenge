<?php

declare(strict_types=1);

// AMQP consumer

require_once __DIR__.'/../vendor/autoload.php';

use Coffreo\Challenge\App;
use Coffreo\Challenge\Amqp\AmqpChannel;
use Coffreo\Challenge\DependencyInjection\Container;

$app = new App(new Container());
$app->build();

$countryChannel = $app->container->get('Country'.AmqpChannel::class);
$countryChannel->consume();
