<?php

declare(strict_types=1);

namespace tests\Coffreo\Challenge\Smoke\Worker;

interface Worker
{
    public function run(): void;

    public function stop(): void;
}
