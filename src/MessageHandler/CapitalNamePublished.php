<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageHandler;

class CapitalNamePublished
{
    public const WAS_INVALID = '';

    public string $capitalName;

    public function __construct(
        mixed $capitalName,
    ) {
        if (false === \is_string($capitalName)) {
            $capitalName = self::WAS_INVALID;
        }
        $this->capitalName = $capitalName;
    }

    public function __toString(): string
    {
        return $this->capitalName;
    }
}
