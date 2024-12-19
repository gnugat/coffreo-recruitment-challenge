<?php

declare(strict_types=1);

namespace tests\Coffreo\Challenge\Unit\MessageHandler;

use Coffreo\Challenge\MessageHandler\CapitalNamePublished;
use Coffreo\Challenge\MessageHandler\CapitalNamePublished\RetrieveCapitalDataForCapitalName;
use Coffreo\Challenge\MessageHandler\CapitalNamePublishedHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class CapitalNamePublishedHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testItPublishesCapitalData(): void
    {
        // Fixtures
        $capitalName = 'Roazhon';
        $capitalData = [
            'name' => $capitalName,
        ];

        // Dummies
        $retrieveCapitalDataForCapitalName = $this->prophesize(RetrieveCapitalDataForCapitalName::class);

        // Stubs & Mocks (make assertions)
        $retrieveCapitalDataForCapitalName->retrieve($capitalName)
            ->shouldBeCalled()->willReturn($capitalData);

        // No final Assertion, as there are no returned value
        $capitalNamePublishedHandler = new CapitalNamePublishedHandler(
            $retrieveCapitalDataForCapitalName->reveal(),
        );
        $capitalNamePublishedHandler->handle(new CapitalNamePublished(
            $capitalName,
        ));
    }

    public function testItIgnoresCapitalNameThatDoNotExist(): void
    {
        // Fixtures
        $capitalName = 'Doomstadt';
        $capitalData = [
            'name' => $capitalName,
        ];

        // Dummies
        $retrieveCapitalDataForCapitalName = $this->prophesize(RetrieveCapitalDataForCapitalName::class);

        // Stubs & Mocks (make assertions)
        $retrieveCapitalDataForCapitalName->retrieve($capitalName)
            ->shouldBeCalled()->willThrow(new \InvalidArgumentException(
                "Invalid country code: should be an existing one, \"{$capitalName}\" given"
            ));

        // No final Assertion, as there are no returned value, and we expect the exception to be caught
        $capitalNamePublishedHandler = new CapitalNamePublishedHandler(
            $retrieveCapitalDataForCapitalName->reveal(),
        );
        $capitalNamePublishedHandler->handle(new CapitalNamePublished(
            $capitalName,
        ));
    }

    public function testItIgnoresCapitalDataThatCannotBeRetrieved(): void
    {
        // Fixtures
        $capitalName = 'Dublin';
        $capitalData = [
            'name' => $capitalName,
        ];

        // Dummies
        $retrieveCapitalDataForCapitalName = $this->prophesize(RetrieveCapitalDataForCapitalName::class);

        // Stubs & Mocks (make assertions)
        $retrieveCapitalDataForCapitalName->retrieve($capitalName)
            ->shouldBeCalled()->willThrow(new \RuntimeException(
                "Couldn't retrieve capital data for capital name \"{$capitalName}\", try again later.",
            ));

        // No final Assertion, as there are no returned value, and we expect the exception to be caught
        $capitalNamePublishedHandler = new CapitalNamePublishedHandler(
            $retrieveCapitalDataForCapitalName->reveal(),
        );
        $capitalNamePublishedHandler->handle(new CapitalNamePublished(
            $capitalName,
        ));
    }
}
