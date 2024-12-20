## Tech Stack

```gherkin
    Scenario: Tech Stack
        When I check the `/doc/PLN-05-tech-stack.md` document
        Then I should see the list of third party libraries used
        And the reason why they were necessary
```

A minimal number of libraries have been added to the project
(as opposed to install the Symfony framework for example),
each one has been added for a specific reason.

## Symfony DotEnv component

In order to follow security best practices, sensitive credentials (RabbitMQ)
[sensitive credentials have been moved to environment variables](https://12factor.net/config).

Following the industry standard, they've been moved to:

* `.env`: a "template" file that details all the variables, but without real values sets
* `.env.local`: a non committed file that's a copy of `.env`, with values suitable for local development
* `.env.test`: a copy of `.env`, but with values suitable for tests

For production, a `.env.prod` file should be created, but not committed.
An encrypted version of it can be encrypted.

To be able to load the correct file for the correct environment, the minimalist
[Symfony DotEnv component](https://symfony.com/doc/current/configuration.html) was installed.

## Monolog

It's good practice for containers to write their logs to the output,
but it's usually recommended to use the Standard Error (STDERR) as it doesn't
buffer and prints debug messages quicker, without mixing them with the standard
output.

Logging Tools (eg Logstash) are also better integrated with STDERR.

To emphasis that the `echo` were indeed meant to be debug logging, a
proper logging library was chosen: [Monolog](https://seldaek.github.io/monolog/).

It has the advantage to allow us to send the logs to a service, if we ever want
to.

> _Note_: Monolog isn't really required, can be removed if it turns out to be
> too much overhead.

## Symfony HTTP Client

The curl requests have been replaced with the
[Symfony HTTP Client](https://symfony.com/doc/current/http_client.html),
which adds support for:

- retries in case of failure
- caching
- performance improvements

> _Note_: to benefit from persistent connection between requests, saving time
> (on DNS lookup, SSL negotiation, etc), the curl extension is enabled.

## PHPUnit

To make sure our service is working as expected, automated tests have been
added. The industry standard [PHPUnit](https://phpunit.de/index.html) framework
was chosen for this task.

To accommodate TDD and more specifically spec BDD, the
[Prophecy mocking library](https://github.com/phpspec/prophecy) has been added.

> _Note_: This is a development dependency , and will not be packaged with the
> production image.

## PHP CS Fixer

The industry standard [PHP CS Fixer](https://cs.symfony.com/) is used to
automatically fix the Coding Standards mistakes, based on
[Coffreo's configured rules](https://github.com/Coffreo/php-cs-fixer-config).

> _Note_: This is a development dependency , and will not be packaged with the
> production image.

## PHP VCR


