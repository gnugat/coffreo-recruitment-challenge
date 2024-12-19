# PLN-04: Refactoring

![What do we want? CAPITALS! When do we want it? CAPITALS!!](./img/PLN-04-refactoring.jpg)

> _The people NEED to get their capital data! We can't fail them._

```gherkin
    Scenario: Refactoring
        When I check the `/doc/PLN-04-refactoring.md` document
        Then I should find a report explaining what changed and why
        And I should see the improvements that were highlited in `/doc/PLN-01-code-review.md`
        And I should see the code changes in the project (commits, `src`, `doc`, `tests`, `bin`, `config`, etc)
        And running `make test` should still have all tests pass successfully
```

This section documents how the  refactoring went. A dev log of some sort.

## Version 0

Our first step is to write a unit test. Scratch that.

Our first step is to write a _Specification_ (test class). It's going to
describe one _Use Case_ (class under test), with one _Example_ (test method).

### CountryCodePublishedHandler

We need to start somewhere, so let's pick `country.php` and try to understand
what we're really trying to achieve there.

The business logic is definitely in `process_message()`, which:

1. gets a country code (which has been published to RabbitMQ)
2. call restcountries.com API to get corresponding country data
3. extract the capital name from country data
4. publishes capital name to RabbitMQ

So I think our entry point will be a "Message Handler", whose role will be to
take care of an event we can call "country code published". Here's what we get:

```php
<?php

declare(strict_types=1);

namespace tests\Coffreo\Challenge\Unit\MessageHandler;

use Coffreo\Challenge\MessageHandler\CountryCodePublishedHandler;
use Coffreo\Challenge\MessageHandler\CountryCodePublished;
use Coffreo\Challenge\MessageHandler\CountryCodePublished\PublishCapitalName;
use Coffreo\Challenge\MessageHandler\CountryCodePublished\RetrieveCapitalNameForCountryCode;
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
            $retrieveCountryFromCode->reveal(),
        );
        $countryCodePublishedHandler->handle(new CountryCodePublished(
            $countryCode,
        ));
    }
}
```

The _Example_ contains four sections:

* **Fixtures**: setting up some values that are used
* **Dummies**: setting up what will be our _Collaborators_ (stubs and mocks)
* **Stubs & Mocks**: describe the interactions with collaborators through _Expectations_ (assertions)
* **Assertion**: setting up our _Use Case_ and calling it with the fixtures

The most important sections when reading the _Example_ is the third bullet
point, and they should describe in almost plain English how the _Use Case_
behaves:

> Retrieve capital name from country code,
> then publish capital name.

The design emerges from this effort to write a descriptive sentence: it
determines what services will be needed and what they'll do.

Running the tests:

```console
make test c='--testsuite unit'
```

Will result in a failure, as the _Collaborator_ classes don't exist.
The easiest way to make them pass is to write empty classes for them, eg:

```php
<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageHandler\CountryCodePublished;

class RetrieveCapitalNameForCountryCode
{
    public function retrieve(string $countryCode): string
    {
        return '';
    }
}
```

The _Use Case_ class don't exist either, so we also create an empty one:

```php
<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageHandler;

class CountryCodePublishedHandler
{
    public function handle(CountryCodePublished $countryCodePublished): void
    {
    }
}
```

Once all the empty classes created, we now get _Expectation_ failures:
`CountryCodePublishedHandler` is not calling the _Collaborators_.

So we write the code to fix that:

```php
<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageHandler;

use Coffreo\Challenge\MessageHandler\CountryCodePublished\PublishCapitalName;
use Coffreo\Challenge\MessageHandler\CountryCodePublished\RetrieveCapitalNameForCountryCode;

class CountryCodePublishedHandler
{
    public function __construct(
        private PublishCapitalName $publishCapitalName,
        private RetrieveCapitalNameForCountryCode $retrieveCapitalNameForCountryCode,
    ) {
    }

    public function handle(CountryCodePublished $countryCodePublished): void
    {
        $capitalName = $this->retrieveCapitalNameForCountryCode->retrieve(
            $countryCodePublished->countryCode
        );
        $this->publishCapitalName->publish($capitalName);
    }
}
```

And that's it, now the testsuite is all green!

We're going to add a couple of "bad case" _Examples_, which all revolve around
exceptions the _Collaborators_ might throw:

```php
<?php
    // [...]

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

    public function testItIgnoresCapitalNamesThatCannotBeRetrieved(): void {}
    public function testItIgnoresCapitalNamesThatCannotBePublished(): void {}
```

Note: we ignore errors here as we expect the _Collaborators_ that throw these
exceptions to log the issues.

A later improvement to the code would be to create custom exceptions.

### CountryCodePublished

With the handler sorted, we can now write a _Specification_ for its Message.

Essentially, the job of `CountryCodePublished` is to validate the input:

```php
<?php

declare(strict_types=1);

namespace tests\Coffreo\Challenge\Unit\MessageHandler;

use Coffreo\Challenge\MessageHandler\CountryCodePublished;
use PHPUnit\Framework\TestCase;

class CountryCodePublishedTest extends TestCase
{
    public function testItAcceptsCca2(): void {}
    public function testItAcceptsCca3(): void {}
    public function testItAcceptsCcn3(): void {}
    public function testItAcceptsCioc(): void {}
    public function testItHasToBeString(): void {}
    public function testItHasToBeBetweenTwoAndThreeCharacters(): void {}
    public function testItHasToBeAlphaNumerical(): void {}
}
```

Note: we don't check that the country code is 100%, just enough for it to be
safely passed to restcountries.com API. If we deem it not valid enough, we
replace it with `000`.

### CapitalNamePublished Message and Handler

Time to take the next _Use Case_, it's quite similar so we won't detail the
tests and code here, we're just going to note the business logic found in
`capital.php`:

1. gets a capital name (which has been published to RabbitMQ)
2. call restcountries.com API to get corresponding country data
3. supposedly extract capital data

So the Specification for the Handler will read as:

> Retrieve capital data from capital name

### HTTP Client

So far, our _Collaborators_ are non functional empty shells.
Let's change that for `RetrieveCapitalDataForCapitalName`.

This service is supposed to make a request to the restcountries.com endpoint.

This is done using `curl_*` functions in `capital.php`, but we're going to do
it using [Symfony HTTP Client component](https://symfony.com/doc/current/http_client.html),
as this will provide us with caching and async capabilities:

```php
<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageHandler\CapitalNamePublished;

use Symfony\Component\HttpClient\HttpClientInterface;

class RetrieveCapitalDataForCapitalName
{
    public function __construct(
        private HttpClientInterface $httpClient,
    ) {
    }

    public function retrieve(string $capitalName): array
    {
        $response = $this->httpClient->request(
            'GET',
            "https://restcountries.com/v3.1/capital/{$capitalName}?fields=capitalInfo",
        );
        $countryData = json_decode($response->getContent(), true);
        $capitalData = [
            'name' => $countryData[0]['capital'],
            'capitalInfo' => $countryData[0]['capitalInfo'],
        ];

        return $capitalData;
    }
}
```

> Note the use of `fields` query parameter in the endpoint, which will help us
> save bandwidth by only getting the data we're interested in.

To be able to verify that it works, we're also going to add logging/debugging
capabilities. This was done in `capital.php` using `echo`s but we're going to
use instead the industry standard [Monolog](https://seldaek.github.io/monolog/),
as this will allow us to:

+ configure where we want the logs to be sent (file, database, email, AWS S3 buckets, etc)
+ format the log message in a standard way (timestamp, level, message)

For this we need to inject the logger as a dependency (relying on the PSR
Interface, to be able to change the logger implementation when we need it),
and call it before the return:

```php
<?php

use Psr\Log\LoggerInterface;

    public function __construct(
        // ...
        private LoggerInterface $logger,
    ) {
    }

    public function retrieve(string $capitalName): array
    {
        // ...

        $this->logger->debug('retrieved capital data for capital name', [
            'capital_name' => $capitalName,
            'capital_data' => $capitalData,
        ]);

        return $capitalData;
    }
}
```

For the record, here's how we've configured Monolog in `Container`:

```php
<?php

declare(strict_types=1);

namespace Coffreo\Challenge;

class Container
{
    // [...]

    public function build(): void
    {
        // [...]

        $logger = new \Monolog\Logger('challenge');
        $logger->pushHandler(new \Monolog\Handler\StreamHandler(
            'php://stderr',
            \Psr\Log\LogLevel::DEBUG,
        ));

        // [...]
    }
}
```

Note how we're using `php://stderr`: we're writing our logs to the standard
error as per the standard recommendation for applications run in containers:
we want to avoid having to write files in the container to keep it stateless,
and it helps not having to deal with file permission issues.

The benefit of STDERR over STDOUT is that it's not buffered (logs appear whole
one at a time, as opposed to partial arbitrary chunk by partial chunk), and
integrates well with monitoring services.

### capital

This makes PublishCapitalNameHandler functionally ready, so let's use it in
`capital.php`:

```php
<?php

// AMQP consumer
require_once __DIR__ . '/../vendor/autoload.php';

use Coffreo\Challenge\App;
use Coffreo\Challenge\Container;
use Coffreo\Challenge\MessageHandler\CapitalNamePublished;
use Coffreo\Challenge\MessageHandler\CapitalNamePublishedHandler;

$app = new App(new Container());
$app->build();
$capitalNamePublishedHandler = $app->container->get(CapitalNamePublishedHandler::class);

// PhpAmqpLib mumbo jumbo [...]

/**
 * @param \PhpAmqpLib\Message\AMQPMessage $message
 */
$processMessage = function($message) use ($capitalNamePublishedHandler) {
    $capitalNamePublishedHandler->handle(
        new CapitalNamePublished($message->body),
    );

    $message->ack();
};

$channel->basic_consume($queue, $consumerTag, false, false, false, false, $processMessage);

// [...]
```

We've done minimal changes to the script:

* instantiate `App`
* retrieved `CapitalNamePublishedHandler` from it,
   in a variable `$capitalNamePublishedHandler`
* make the `process_message()` function anonymous,
  allowing us to bind `$capitalNamePublishedHandler` from parent scope (no use of `global`)
* replaced the body of `process_message()` with just calling `$capitalNamePublishedHandler`,
  converting the `AMQPMessage` into `CapitalNamePublished`

With the writing to STDOUT removed and replace with STDERR logging,
we can remove from `CapitalWorker` the writing to `/tmp/capital.log`,
monitoring the logs of `capital.php` is now done as follow:

```console
make logs # or docker composer logs --tail=0 --follow
```

Now we're going to implement similar changes for country: use HTTP Client and
Logger in `RetrieveCapitalNameForCountryCode`.

### PublishCapitalName

This leaves us with `PublishCapitalName` to implement.
