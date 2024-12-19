<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageHandler;

class CountryCodePublished
{
    public const WAS_INVALID = '000';

    public string $countryCode;

    public function __construct(
        mixed $countryCode,
    ) {
        if (false === \is_string($countryCode)) {
            $countryCode = self::WAS_INVALID;
        }
        $length = \strlen($countryCode);
        if ($length < 2 || $length > 3) {
            $countryCode = self::WAS_INVALID;
        }
        if (false === ctype_alnum($countryCode)) {
            $countryCode = self::WAS_INVALID;
        }
        $this->countryCode = $countryCode;
    }

    public function __toString(): string
    {
        return $this->countryCode;
    }
}
