<?php

declare(strict_types=1);

namespace tests\Coffreo\Challenge\Unit\MessageHandler;

use Coffreo\Challenge\MessageHandler\CountryCodePublished;
use Coffreo\Challenge\MessageHandler\CountryCodePublished\PublishCapitalName;
use Coffreo\Challenge\MessageHandler\CountryCodePublished\RetrieveCapitalNameForCountryCode;
use Coffreo\Challenge\MessageHandler\CountryCodePublishedHandler;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;

class CountryCodePublishedHandlerTest extends TestCase
{
    use ProphecyTrait;

    public function testItPublishesCapitalName(): void
    {
        // Fixtures
        $countryCode = 'BZH';
        $capitalName = 'Roazhon';

        // Dummies
        $retrieveCapitalNameForCountryCode = $this->prophesize(RetrieveCapitalNameForCountryCode::class);
        $publishCapitalName = $this->prophesize(PublishCapitalName::class);

        // Stubs & Mocks (make assertions)
        $retrieveCapitalNameForCountryCode->retrieve($countryCode)
            ->shouldBeCalled()->willReturn($capitalName);
        $publishCapitalName->publish($capitalName)
           ->shouldBeCalled();

        // No final Assertion, as there are no returned value
        $countryCodePublishedHandler = new CountryCodePublishedHandler(
            $publishCapitalName->reveal(),
            $retrieveCapitalNameForCountryCode->reveal(),
        );
        $countryCodePublishedHandler->handle(new CountryCodePublished(
            $countryCode,
        ));
    }

    public function testItIgnoresCountryCodeThatDoNotExist(): void
    {
        // Fixtures
        $countryCode = 'LAT'; // Latveria, Dr. Doom's Kingdom
        $capitalName = 'Doomstadt';

        // Dummies
        $retrieveCapitalNameForCountryCode = $this->prophesize(RetrieveCapitalNameForCountryCode::class);
        $publishCapitalName = $this->prophesize(PublishCapitalName::class);

        // Stubs & Mocks (make assertions)
        $retrieveCapitalNameForCountryCode->retrieve($countryCode)
            ->shouldBeCalled()->willThrow(new \InvalidArgumentException(
                "Invalid country code: should be an existing one, \"{$countryCode}\" given"
            ));
        $publishCapitalName->publish($capitalName)
           ->shouldNotBeCalled();

        // No final Assertion, as there are no returned value, and we expect the exception to be caught
        $countryCodePublishedHandler = new CountryCodePublishedHandler(
            $publishCapitalName->reveal(),
            $retrieveCapitalNameForCountryCode->reveal(),
        );
        $countryCodePublishedHandler->handle(new CountryCodePublished(
            $countryCode,
        ));
    }

    public function testItIgnoresCapitalNamesThatCannotBeRetrieved(): void
    {
        // Fixtures
        $countryCode = 'IRE';
        $capitalName = 'Dublin';

        // Dummies
        $retrieveCapitalNameForCountryCode = $this->prophesize(RetrieveCapitalNameForCountryCode::class);
        $publishCapitalName = $this->prophesize(PublishCapitalName::class);

        // Stubs & Mocks (make assertions)
        $retrieveCapitalNameForCountryCode->retrieve($countryCode)
            ->shouldBeCalled()->willThrow(new \RuntimeException(
                "Couldn't retrieve capital name for country code \"{$countryCode}\", try again later.",
            ));
        $publishCapitalName->publish($capitalName)
           ->shouldNotBeCalled();

        // No final Assertion, as there are no returned value, and we expect the exception to be caught
        $countryCodePublishedHandler = new CountryCodePublishedHandler(
            $publishCapitalName->reveal(),
            $retrieveCapitalNameForCountryCode->reveal(),
        );
        $countryCodePublishedHandler->handle(new CountryCodePublished(
            $countryCode,
        ));
    }

    public function testItIgnoresCapitalNamesThatCannotBePublished(): void
    {
        // Fixtures
        $countryCode = 'LCA';
        $capitalName = 'Castries';

        // Dummies
        $retrieveCapitalNameForCountryCode = $this->prophesize(RetrieveCapitalNameForCountryCode::class);
        $publishCapitalName = $this->prophesize(PublishCapitalName::class);

        // Stubs & Mocks (make assertions)
        $retrieveCapitalNameForCountryCode->retrieve($countryCode)
            ->shouldBeCalled()->willReturn($capitalName);
        $publishCapitalName->publish($capitalName)
            ->shouldBeCalled()->willThrow(new \RuntimeException(
                "Couldn't publish capital name \"{$capitalName}\", try again later.",
            ));

        // No final Assertion, as there are no returned value, and we expect the exception to be caught
        $countryCodePublishedHandler = new CountryCodePublishedHandler(
            $publishCapitalName->reveal(),
            $retrieveCapitalNameForCountryCode->reveal(),
        );
        $countryCodePublishedHandler->handle(new CountryCodePublished(
            $countryCode,
        ));
    }
}
