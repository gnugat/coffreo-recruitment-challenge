# Using dunglas/symfony-docker Maklefile template
# Executables (local)
DOCKER_COMP = docker compose

# Docker containers
PHP_CONT = $(DOCKER_COMP) exec challenge
RABBITMQ_CONT = $(DOCKER_COMP) exec rabbitmq

# Executables
PHP      = $(PHP_CONT) php
COMPOSER = $(PHP_CONT) composer
PHPUNIT = $(PHP) vendor/bin/phpunit
PHP_CS_FIXER = $(PHP) vendor/bin/php-cs-fixer

# Misc
.DEFAULT_GOAL = help
.PHONY        : help build up start down bash-php bash-rabbitmq test docker-build docker-run docker-sh composer

## —— Usage ℹ️  —————————————————————————————————————————————————————————————————
help: ## Outputs this help screen
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— Docker 🐳 ————————————————————————————————————————————————————————————————
build: ## Builds the Docker images
	@$(DOCKER_COMP) build --pull --no-cache

up: ## Start the docker hub in detached mode (no logs)
	@$(DOCKER_COMP) up --detach

start: build up ## Build and start the containers

down: ## Stop the docker hub
	@$(DOCKER_COMP) down --remove-orphans

logs: ## Show live logs, pass the parameter "c=" to add options, example: make logs c='challenge'
	@$(eval c ?=)
	@$(DOCKER_COMP) logs --tail=0 --follow $(if $(c),$(c),)

## —— PHP 🐘 ———————————————————————————————————————————————————————————————————
bash-php: ## Connect to the challenge (PHP) container via bash so up and down arrows go to previous commands
	@$(PHP_CONT) bash

## —— Quality 🛂 ———————————————————————————————————————————————————————————————
test: ## Start tests with phpunit, pass the parameter "c=" to add options to phpunit, example: make test c="--group smoke --stop-on-failure"
	@$(eval c ?=)
	@$(PHPUNIT) $(if $(c),$(c),)

fix-cs: ## Start PHP CS Fixer, pass the parameter "c=" to add options, example: make fix-cs c="--dry-run"
	@$(eval c ?=)
	@$(PHP_CS_FIXER) fix $(if $(c),$(c),)

## —— Composer 🧙 ——————————————————————————————————————————————————————————————
composer: ## Run composer, pass the parameter "c=" to run a given command, example: make composer c='require --dev phpunit/phpunit:^10.5'
	@$(eval c ?=)
	@$(COMPOSER) $(c)

vendor: ## Install vendors according to the current composer.lock file
vendor: c=install --prefer-dist --no-dev --no-progress --no-scripts --no-interaction
vendor: composer

## —— RabbitMQ 🐇 ——————————————————————————————————————————————————————————————

bash-rabbitmq: ## Connect to the RabbitMQ container via bash so up and down arrows go to previous commands
	@$(RABBITMQ_CONT)) bash

#  Legacy / Aliases ————————————————————————————————————————————————————————————
docker-build:
	@$(DOCKER_COMP) build
docker-run: up
docker-sh: bash-php
## —————————————————————————————————————————————————————————————————————————————
