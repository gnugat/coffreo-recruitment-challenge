<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageHandler;

use Coffreo\Challenge\MessageHandler\CapitalNamePublished\RetrieveCapitalDataForCapitalName;

class CapitalNamePublishedHandler
{
    public function __construct(
        private RetrieveCapitalDataForCapitalName $retrieveCapitalDataForCapitalName,
    ) {
    }

    public function handle(CapitalNamePublished $capitalNamePublished): void
    {
        try {
            $capitalData = $this->retrieveCapitalDataForCapitalName->retrieve(
                $capitalNamePublished->capitalName
            );
        } catch (\Exception $e) {
            return;
        }
    }
}
