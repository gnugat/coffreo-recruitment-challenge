FROM php:8.2-bullseye AS php

# Install dependencies
RUN set -eux \
    && apt-get update \
    && apt-get install -y --no-install-recommends \
        git \
        unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install zip and sodium extensions
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

# Install composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www

# Copy source code
COPY . .

CMD ["tail", "-f", "/dev/null"]
