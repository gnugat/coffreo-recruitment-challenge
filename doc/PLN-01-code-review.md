# Code Review Report

![Michael Scott, from the American TV show The Office, looking rather annoyed](./img/PLN-01-code-review.gif)

> _Pictured above: my reaction to seeing procedural PHP._

```gherkin
    Scenario: Code Review
        When I check the `/doc/PLN-01-code-review.md` document
        Then I should find an assessment of the code that identifies:
            - bugs
            - securitry issues
            - performance issues
            - quality issues
            - scalability issues
        And specifies solutions as to how to address them
```

In this project, we have two PHP workers:

* `/src/country.php`, a first AMQP consumer which:
    - expects producers to send messages containing country codes to the `router` exchange
    - picks these messages from the `country` queue and extracts the country code (eg `FR`)
    - queries the REST countries API, using the code endpoint
    - extracts the capital name from the country response (eg `Paris`)
    - publishes a message containing the capital name to the `router` exchange, for the `capital` queue
* `/src/capital.php`, a second AMQP consumer which:
    - expects producers to send messages containing capital names to the `router` exchange
    - picks these messages from the `capital` queue and extracts the capital name (eg `Paris`)
    - queries the REST countries API, using the capital endpoint
    - prints the country data from the response to the standard output

> **Note**: The [REST countries API](https://restcountries.com/) is used.
>
> Its "code" endpoint (`GET /v3.1/alpha/{code}`) accepts several formats for the "country code":
> - `cca2`: ISO 3166-1 alpha-2 two-letter country codes - eg `FR`, `US`, etc
> - `cca3`: ISO 3166-1 alpha-3 three-letter country codes - eg `FRA`, `USA`, etc
> - `ccn3`: ISO 3166-1 numeric code (UN M49) - eg `250`, `840`, etc
> - `cioc`: Code of the International Olympic Committee - eg `FRA`, `USA`, etc
>
> Its "capital" endpoint (`GET /v3.1/capital/{capital}`) accepts URL encoded capital names:
> - `Paris`, for France's capital
> - `Washington%2C%20D.C.` for the USA's capital (`Washington, D.C.`)

## Assessment

In terms of code structure those files follow the same format, so most feedback
will apply to them both:

- **quality**: debugging done in console output,
  missing tests / coding style / static analysis,
  code written in procedural
  with poor organization of logic (different concerns are mixed)
  and tight coupling (causes limited reusability and testing challenges)
    + should use a logger for debugging (in an asynchronous manner, eg AWS S3, ELK, etc)
    + should have an automated testsuite, PHPUnit is a good industry standard
    + should have Coding Standards rules agreed, PHP CS Fixer to enforce them
    + should have Static Analysis tool, phpstan is a good industry standard
    + should extract configuration parameters in their own files
    + should extract RabbitMQ set up logic
    + should extract HTTP client logic
    + should extract Message Processing (business logic) in their own classes
- **bugs**: lack sanitizing and validation of inputs, as well as error handling
    + should check that input isn't missing and conforms to expected format
    + should check that the external API call didn't fail
    + should implement retry mechanism, with timeouts
    + should check the validity of the data returned by the external API
    + should log errors to allow catching live issues and debugging them
- **security**: hard coded and committed sensitive credentials, default RabbitMQ settings used
    + [should be moved to environment variables](https://12factor.net/config)
    + should be managed by a Super Secret System (commit encrypted credentials)
    + should not use `guest/guest` RabbitMQ credentials for production
    + should make sure to use SSL for the RabbitMQ connection
- **performance**: bloated and synchronous/blocking HTTP calls
    + should make use of the API's `fields` parameter to only get required data
    + should consider caching the API's responses (with a validation/expiration strategy)
    + should pool HTTP requests and asynchronously handle the responses
- **scalability**: serial Message Processing
    + should use a process supervisor to spin multiple workers and manage them

Specific to `/src/capital.php`:

- **bug 00**: getting all country data instead of capital specific data
  (this is already what `country.php` does);
  should already be addressed in the following items, but still worth noting
    + _should make use of the API's `fields` parameter to only get required data_
    + _should consider caching the API's responses (with a validation/expiration strategy)_
- **bug 01**: capital data isn't stored anywhere, just written on the console input
    + should at least be logged, or alternatively be persisted in some way (database, etc)
- **performance**: use of `var_export` for serialization, and outputting to the console
    + should use `$data` as is (it's a string), or use proper serializer like `json_encode`
      (`var_export` is meant for debugging, it serializes the value of a variable)

> **Note**: a conversation needs to be had to make sure that `capital.php`'s
> current behaviour is what's expected of it, given **bug 00** and **bug 01**,
> ie:
>
> - **bug 00**: getting all country data instead of capital specific data
> - **bug 01**: capital data isn't stored anywhere, just written on the console output

Specific to `/src/country.php`:

- **quality**: use of `global` variable (causes limited reusability and testing challenges)
    + should instead pass variables from the parent scope to the anonymous function with `use`

Specific to `/Dockerfile`:

- **quality**: is missing `composer` dependencies,
  has repeated `RUN` blocks for `apt-get` which increases image build time and size
    + should combine dependency installation into a single `RUN` block
    + should run `composer install -o` (with autoloader optimization)
- **security**: using unsupported versions
    + should upgrade from PHP version `8.0` (EOL was 03 Aug 23)
      to at least `8.1` (was active until 25 Nov 23, security supported until 31 Dec 25),
      `8.3` recommended (active support until 31 Dec 25, security supported until 31 Dec 27)
      see [PHP supported versions](https://www.php.net/supported-versions.php)

Specific to `/docker-compose.yaml`:

- **scalability**: only one container for all the workers
    + should configure one container per worker
- **quality**: lack of explicit dependency between `challenge` and `rabbitmq`
    + should add a `challenge.depends_on: rabbitmq` to ensure proper order of startup

Specific to `/composer.json`:

- **quality**: missing explicitly PHP version constraint
    + should enforce `php` version compatibility
