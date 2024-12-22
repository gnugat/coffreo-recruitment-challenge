<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageHandler\CountryCodePublished;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class RetrieveCapitalNameForCountryCode
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {
    }

    public function retrieve(string $countryCode): string
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                "https://restcountries.com/v3.1/alpha/{$countryCode}?fields=capital",
            );
        } catch (HttpExceptionInterface $e) {
            $this->logger->warning('failed to retrieve capital name for country code', [
                'capital_name' => $capitalName,
                'error' => $e->getMessage(),
            ]);

            throw new \InvalidArgumentException("Invalid country code: should be an existing one, \"{$countryCode}\" given", $e->getode(), $e);
        }
        $countryData = json_decode($response->getContent(), true);
        $capitalName = $countryData['capital'][0] ?? null;
        if (null === $capitalName) {
            $this->logger->warning('failed to retrieve capital name for country code', [
                'country_code' => $countryCode,
                'capital_name' => $capitalName,
            ]);

            throw new \InvalidArgumentException("Invalid country code: should be an existing one, \"{$countryCode}\" given");
        }

        $this->logger->debug('retrieved capital name for country code', [
            'country_code' => $countryCode,
            'capital_name' => $capitalName,
        ]);

        return $capitalName;
    }
}
