<?php

declare(strict_types=1);

namespace tests\Coffreo\Challenge\Unit\MessageHandler;

use Coffreo\Challenge\MessageHandler\CapitalNamePublished;
use PHPUnit\Framework\TestCase;

class CapitalNamePublishedTest extends TestCase
{
    public function testItAcceptsCapitalNames(): void
    {
        // Fixtures
        $capitalNames = [
            'Lisbon', 'Madrid', 'Brussel', 'Luxembourg',
        ];

        // Assertion
        foreach ($capitalNames as $capitalName) {
            $this->assertSame(
                $capitalName,
                (new CapitalNamePublished($capitalName))->capitalName,
            );
        }
    }

    public function testItHasToBeString(): void
    {
        // Fixtures
        $capitalNames = [
            -1, 0, 1, 42, null, 0.0, [], true, false,
        ];

        // Assertion
        foreach ($capitalNames as $capitalName) {
            $this->assertSame(
                CapitalNamePublished::WAS_INVALID,
                (new CapitalNamePublished($capitalName))->capitalName,
            );
        }
    }
}
