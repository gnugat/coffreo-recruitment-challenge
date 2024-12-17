# Automated Tests

> _Yo, [I herd you like tests](https://mnapoli.fr/i-herd-you-like-tests)?
> Then [don't forget to SpecSpec your SpecSpec](https://youtu.be/2Ia4kVL53KA?t=324)!_
>
> -- Someone on the internet.

```gherkin
    Scenario: Automated Tests
        When I check the `/doc/PLN-03-automated-tests.md` document
        Then I should find steps describing how to run the test suite
        And running `make test` should have all tests pass successfully
```

The industry standard [PHPUnit](https://phpunit.de/index.html) is used for the
automated tests. The testsuite can be run as follow:

```console
make test # use `c='<options>'` to pass options, eg: `c='--stop-on-failure'`
```

The industry standard [PHP CS Fixer](https://cs.symfony.com/) is used to
automatically fix the Coding Standards mistakes, based on
[Coffreo's configured rules](https://github.com/Coffreo/php-cs-fixer-config).

Run it as follow:

```console
make fix-cs # use `c='<options>'` to pass options, eg: `c='--dry-run'`
```

## Version 1

In Version 1, we're adding **Unit Tests**, or more precisely _specifications_,
following the specBDD (Behaviour Driven Development) terminology.

They are written first, and describe the use cases and its examples we want to
achieve , through the use of collaborators (ie Mocks and Stubs).

### Unit Tests

The unit tests can be found in `/tests/Unit`, and can be specifically run as
follow:

```console
make test c='--testsuite unit'
```

> **Note**: The unit tests are failing in Version 1, as the code they're
> testing doesn't exist yet. It'll be implemented in the refactoring phase.

To enable the specBDD approach, we've required:

* [Prophecy, the highly opinionated mocking library](https://github.com/phpspec/prophecy)
* as well as [its PHPUnit integration](https://github.com/phpspec/prophecy-phpunit)

## Version 0

For this first version, we're going to write:

* **Smoke Tests**, which outline how the workers currently work and what's
  expected of them, to prevent regression through the coming refactorings, as
  well as outline issues to be fixed. All this without delving too much in the
  details
* **Fixtures**, which are intended for manual testing, and the benchmarks
* **Benchmarking**, which establish a baseline to ensure performance don't
  degrade through the coming refactorings

To help improve the quality of the project, we're also going to install and configure:

+ [PHP CS Fixer](https://cs.symfony.com/), this will automatically fix the
  Coding Standards mistakes,
  based on [Coffreo's configured rules](https://github.com/Coffreo/php-cs-fixer-config)

### Smoke Tests

The smoke tests can be found in `/tests/Smoke`, and can be specifically run as
follow:

```console
make test c='--testsuite smoke'
```

In these tests, we:

1. launch the application (here the workers)
2. throw some relevant inputs at it
3. verify the absence of errors

It's in a way like end to end testing (the full application is run),
but we don't check any details (eg check the logs): the absence of error
is enough for the test to be considered as passed.

> **Note**: The smoke tests are slow in Version 0, as they run the actual
> workers which in turn are slow.
> More than a weakness on this type of testing, the slowness here is more the
> symptom of a problem the project is having and it highlights that it needs
> to be fixed by improving the workers' performance.

To be able to run the PHP workers (`country.php` and `capital.php`) from the
PHP tests, in the background, we've required the
[Symfony Process component](https://symfony.com/doc/current/components/process.html).

This also required the creation of a couple of "structural" classes:

* `/src/App.php`, to provide access to the Dependency Injection Container (DIC)
* `/src/Container.php`, to build and provide the different services,
  will have to be replaced by a proper third party library later
* `/src/MessageQueue/`, a poor man's RabbitMQ wrapper,
  will have to be replaced by a proper third party library later
* `/src/Worker/`, currently just running their respective
  `/src/country.php` and `/src/capital.php` scripts,
  but will be refactored to follow best practices

> _Note_: with the creation of `Container`, took the opportunity to start using
> environment variables (for the RabbitMQ configuration).
> These are currently stored in `/.env`, and to help load its values we've
> installed [Symfony's DotEnv component](https://github.com/symfony/symfony/tree/7.3/src/Symfony/Component/Dotenv#dotenv-component).

Also note the creation of `/tests/AppSingleton.php`, which is here to avoid
having to rebuild the container between every tests (for each test method,
PHPUnit instantiates by design a new instance of the test class, so it's not
possible to share an instance of a non static test class property between its
methods).

### Fixtures

Prepare some testing inputs:

```console
# Retrieving valid inputs
## country
curl -s https://restcountries.com/v3.1/all?fields=cca2 | jq -r '.[].cca2' | grep -v null > /tmp/valid-cca2.txt
curl -s https://restcountries.com/v3.1/all?fields=cca3 | jq -r '.[].cca3' | grep -v null > /tmp/valid-cca3.txt
curl -s https://restcountries.com/v3.1/all?fields=ccn3 | jq -r '.[].ccn3' | grep -v null > /tmp/valid-ccn3.txt
curl -s https://restcountries.com/v3.1/all?fields=cioc | jq -r '.[].cioc' | grep -v null > /tmp/valid-cioc.txt
## capital
curl -s https://restcountries.com/v3.1/all?fields=capital | jq -r '.[].capital' | grep -v null > /tmp/valid-capitals.txt

# Generating invalid inputs
## empty whitespace
echo -e "\n " > /tmp/invalid-inputs.txt
## random strings
for i in {1..250}; do
    # Random length between 1 and 10
    length=$(shuf -i 1-10 -n 1)
    # Generate a random string of that length
    cat /dev/urandom | tr -dc 'a-zA-Z!@#$%^&*()_+[]{}|:;,.<>?' | fold -w $length | head -n 1
done >> /tmp/invalid-inputs.txt

# Combining to create test inputs
## country
cat /tmp/valid-cca2.txt > /tmp/test-inputs-country.txt
cat /tmp/valid-cca3.txt >> /tmp/test-inputs-country.txt
cat /tmp/valid-ccn3.txt >> /tmp/test-inputs-country.txt
cat /tmp/valid-cioc.txt >> /tmp/test-inputs-country.txt
cat /tmp/invalid-inputs.txt >> /tmp/test-inputs-country.txt
## capital
cat /tmp/valid-capitals.txt > /tmp/test-inputs-capital.txt
cat /tmp/invalid-inputs.txt >> /tmp/test-inputs-capital.txt
```

### Benchmarking

The following will send 100 requests, and print in seconds how long
the workers took to process all the messages (from country to capital):

```console
# Setup
REQUESTS=100

# Starting Stopwatch
STOPWATCH_START=$(stat --format='%Y' /tmp/country.log)

# Sending loads of messages to **country** (the entry point) 
shuf -n $REQUESTS /tmp/test-inputs-country.txt | \
    xargs -I PAYLOAD -P100 sh -c \
    'curl -s -o /dev/null -u guest:guest -H "Content-Type: application/json" -X POST -d "{\"routing_key\": \"country\", \"payload\": \"PAYLOAD\", \"exchanges\": \"router\", \"properties\":{}, \"payload_encoding\":\"string\"}" http://localhost:15672/api/exchanges/%2f/router/publish'

# Waiting for **capital** to finish (the exit point)
PREV_LOG_LINES=0
while true; do
  CUR_LOG_LINES=$(wc -l < /tmp/capital.log)
  if [[ $CUR_LOG_LINES -eq $PREV_LOG_LINES ]]; then
    # Stop Stopwatch
    STOPWATCH_END=$(stat --format='%Y' /tmp/capital.log)

    # Display Bencharmk Results
    SECONDS=$((STOPWATCH_END - STOPWATCH_START))
    echo "Benchmarks results"
    echo "  Requests: $REQUESTS"
    echo "  Seconds: $SECONDS"
    echo "  Requests / Seconds: $(bc <<<"scale=3; $REQUESTS / $SECONDS")"
    break
  fi
  PREV_LOG_LINES=$CUR_LOG_LINES
  sleep 5
done
```

After running it for a couple of times, the result was an average of:

* 0.8 Request / Seconds

This number will be kept to establish some baseline for the future versions.
