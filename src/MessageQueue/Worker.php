<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageQueue;

interface Worker
{
    public function run(): void;

    public function stop(): void;
}
