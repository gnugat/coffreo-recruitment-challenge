<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageHandler;

use Coffreo\Challenge\MessageHandler\CountryCodePublished\PublishCapitalName;
use Coffreo\Challenge\MessageHandler\CountryCodePublished\RetrieveCapitalNameForCountryCode;

class CountryCodePublishedHandler
{
    public function __construct(
        private PublishCapitalName $publishCapitalName,
        private RetrieveCapitalNameForCountryCode $retrieveCapitalNameForCountryCode,
    ) {
    }

    public function handle(CountryCodePublished $countryCodePublished): void
    {
        try {
            $capitalName = $this->retrieveCapitalNameForCountryCode->retrieve(
                $countryCodePublished->countryCode
            );
        } catch (\Exception $e) {
            return;
        }
        try {
            $this->publishCapitalName->publish($capitalName);
        } catch (\Exception $e) {
            return;
        }
    }
}
