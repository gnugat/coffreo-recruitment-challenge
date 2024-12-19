<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageHandler\CapitalNamePublished;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface;

class RetrieveCapitalDataForCapitalName
{
    public function __construct(
        private HttpClientInterface $httpClient,
        private LoggerInterface $logger,
    ) {
    }

    public function retrieve(string $capitalName): array
    {
        try {
            $response = $this->httpClient->request(
                'GET',
                "https://restcountries.com/v3.1/capital/{$capitalName}?fields=capitalInfo",
            );
        } catch (HttpExceptionInterface $e) {
            $this->logger->debug('failed to retrieve capital data for capital name', [
                'capital_name' => $capitalName,
                'error' => $e->getMessage(),
            ]);

            throw new \InvalidArgumentException(
                "Invalid capital name: should be an existing one, \"{$capitalName}\" given",
                $e->getode(),
                $e,
            );
        }
        $countryData = json_decode($response->getContent(), true);
        $capitalData = [
            'name' => $capitalName,
            'capitalInfo' => $countryData[0]['capitalInfo'],
        ];

        $this->logger->debug('retrieved capital data for capital name', [
            'capital_name' => $capitalName,
            'capital_data' => $capitalData,
        ]);

        return $capitalData;
    }
}
