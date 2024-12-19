<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageHandler\CountryCodePublished;

use Coffreo\Challenge\Amqp\AmqpChannel;
use Coffreo\Challenge\MessageHandler\CapitalNamePublished;

class PublishCapitalName
{
    public function __construct(
        private AmqpChannel $capitalAmqpChannel,
    ) {
    }

    public function publish(string $capitalName): void
    {
        $this->capitalAmqpChannel->publish(
            new CapitalNamePublished($capitalName),
        );
    }
}
