<?php

declare(strict_types=1);

namespace Coffreo\Challenge\DependencyInjection;

class Container
{
    public function __construct(
        public array $services = [],
        public array $parameters = [],
    ) {
    }

    public function build(): void
    {
        ServiceBuilder\HttpClientServiceBuilder::build($this);
        ServiceBuilder\LoggerServiceBuilder::build($this);
        ServiceBuilder\AmqpServiceBuilder::build($this);
        ServiceBuilder\CapitalServiceBuilder::build($this);
        ServiceBuilder\CountryServiceBuilder::build($this);
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
