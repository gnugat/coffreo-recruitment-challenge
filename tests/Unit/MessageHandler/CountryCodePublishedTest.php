<?php

declare(strict_types=1);

namespace tests\Coffreo\Challenge\Unit\MessageHandler;

use Coffreo\Challenge\MessageHandler\CountryCodePublished;
use PHPUnit\Framework\TestCase;

class CountryCodePublishedTest extends TestCase
{
    public function testItAcceptsCca2(): void
    {
        // Fixtures
        $countryCodes = [
            'GS', 'GD', 'CH', 'SL', 'HU', 'TW', 'WF', 'BB', 'PN', 'CI',
        ];

        // Assertion
        foreach ($countryCodes as $countryCode) {
            $this->assertSame(
                $countryCode,
                (new CountryCodePublished($countryCode))->countryCode,
            );
        }
    }

    public function testItAcceptsCca3(): void
    {
        // Fixtures
        $countryCodes = [
            'SGS', 'GRD', 'CHE', 'SLE', 'HUN', 'TWN', 'WLF', 'BRB', 'PCN', 'CIV',
        ];

        // Assertion
        foreach ($countryCodes as $countryCode) {
            $this->assertSame(
                $countryCode,
                (new CountryCodePublished($countryCode))->countryCode,
            );
        }
    }

    public function testItAcceptsCcn3(): void
    {
        // Fixtures
        $countryCodes = [
            '239', '308', '756', '694', '348', '158', '876', '052', '612', '384',
        ];

        // Assertion
        foreach ($countryCodes as $countryCode) {
            $this->assertSame(
                $countryCode,
                (new CountryCodePublished($countryCode))->countryCode,
            );
        }
    }

    public function testItAcceptsCioc(): void
    {
        // Fixtures
        $countryCodes = [
            'GRN', 'SUI', 'SLE', 'HUN', 'TPE', 'BAR', 'CIV',
        ];

        // Assertion
        foreach ($countryCodes as $countryCode) {
            $this->assertSame(
                $countryCode,
                (new CountryCodePublished($countryCode))->countryCode,
            );
        }
    }

    public function testItHasToBeString(): void
    {
        // Fixtures
        $countryCodes = [
            -1, 0, 1, 42, null, 0.0, [], true, false,
        ];

        // Assertion
        foreach ($countryCodes as $countryCode) {
            $this->assertSame(
                CountryCodePublished::WAS_INVALID,
                (new CountryCodePublished($countryCode))->countryCode,
            );
        }
    }

    public function testItHasToBeBetweenTwoAndThreeCharacters(): void
    {
        // Fixtures
        $countryCodes = [
            '', 'a', 'blob', 'too long', 'not even close',
        ];

        // Assertion
        foreach ($countryCodes as $countryCode) {
            $this->assertSame(
                CountryCodePublished::WAS_INVALID,
                (new CountryCodePublished($countryCode))->countryCode,
            );
        }
    }

    public function testItHasToBeAlphaNumerical(): void
    {
        // Fixtures
        $countryCodes = [
            "\n\n", '$$', '_-', '😾', '  ',
        ];

        // Assertion
        foreach ($countryCodes as $countryCode) {
            $this->assertSame(
                CountryCodePublished::WAS_INVALID,
                (new CountryCodePublished($countryCode))->countryCode,
            );
        }
    }
}
