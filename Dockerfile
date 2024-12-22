# PHP image, for dev environment
FROM php:8.2-bullseye AS php_dev

# Install in one RUN
RUN set -eux \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
        libzip-dev \
        libsodium-dev \
        librabbitmq-dev \
    && docker-php-ext-install \
        zip \
        sodium \
        bcmath \
        sockets \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www

# PHP image, for dev environment
FROM php:8.2-bullseye AS php_prod

# Install only required production dependencies
RUN set -eux \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        libzip-dev \
        libsodium-dev \
        librabbitmq-dev \
    && docker-php-ext-install \
        zip \
        sodium \
        bcmath \
        sockets \
    && pecl install amqp \
    && docker-php-ext-enable amqp \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www

# Copy only necessary files
# (we need to define a .env.prod instead of using .env)
COPY composer.* ./
COPY src config .env ./

USER www-data

# Use supervisor to manage processes
RUN apt-get update && apt-get install -y supervisor \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

COPY docker/supervisor/workers.conf /etc/supervisor/conf.d/

CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
