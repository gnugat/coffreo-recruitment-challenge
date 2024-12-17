## Executable Application

> 🐳 Using Docker to install, run and deploy the application.

```gherkin
    Scenario: Executable Application
        When I check the `/doc/PLN-02-executable-application.md` document
        Then I should find steps describing how to install and run the application
```

## Version 0

> This section concerns the application as it was originally provided,
> ""Version 0".

Requirements:

* [docker](https://www.docker.com/) and `make`
  (see `./PLN-02/v0-00-docker-and-make-installation.md`)

First, setup the project by running:

* `make docker-build` (or `docker compose build`)
  to build the docker images (or rebuild when `Dockerfile` is changed)
* `docker compose run --rm challenge composer install --optimize-autoloader`
  to install PHP libraries dependencies, in an optimized way
* `make docker-run` (or `docker compose up -d`)
  to start the services (eg RabbitMQ) in the background

Next, launch each worker in its own window:

* `docker compose exec challenge php /var/www/src/country.php > /tmp/country.log 2>&1`
  to launch the country worker (in window 0)
* `tail -n 0 -f /tmp/country.log`
  to monitor country console output (in window 1)
* `docker compose exec challenge php /var/www/src/capital.php > /tmp/capital.log 2>&1`
  to launch the capital worker, and monitor its console output (in window 2)
* `tail -n 0 -f /tmp/capital.log`
  to monitor capital console output (in window 3)

After that, open the interactive shell in the rabbitmq container,
and run the `rabbitmqadmin` CLI tool to publish test messages to the queues:

* `docker compose exec rabbitmq bash`
  to open the interactive shell (in window 4)
* `rabbitmqadmin publish exchange=router routing_key=country payload=FR`
  to publish messages for the country worker to consume
* `rabbitmqadmin publish exchange=router routing_key=capital payload=Paris`
  to publish messages for the capital worker to consume
* `exit`
  to exit the interactive shell (or use Ctrl+D)

> _Note_: It's also possible to do that via curl request:
>
> ```console
> curl -u guest:guest \
>     -H "Content-Type: application/json" \
>     -X POST \
>     -d '{"routing_key": "country", "payload": "FR", "exchanges": "router", "properties":{}, "payload_encoding":"string"}' \
>     http://localhost:15672/api/exchanges/%2f/router/publish
> curl -u guest:guest \
>     -H "Content-Type: application/json" \
>     -X POST \
>     -d '{"routing_key": "capital", "payload": "London", "exchanges": "router", "properties":{}, "payload_encoding":"string"}' \
>     http://localhost:15672/api/exchanges/%2f/router/publish
> ```

By switching back to windows 1 and 3, we should be able to see debug messages
on the console output, confirming the messages where published and consumed
by their respective workers.

> _Note_: [tmux](https://github.com/tmux/tmux/wiki) can be used locally to
> go back and forth between the windows. See `/doc/PLN-02-01-tmux.md`

Finally, when finished, you can run:

* `docker compose down`
  to stop the services (eg RabbitMQ)
