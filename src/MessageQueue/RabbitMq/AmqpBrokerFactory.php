<?php

declare(strict_types=1);

namespace Coffreo\Challenge\MessageQueue\RabbitMq;

/**
 * Creates an instance of AmqpBroker, from a DSN configuration.
 */
class AmqpBrokerFactory
{
    /**
     * The `amqp` URI scheme:
     *
     * ```
     * amqp_URI       = "amqp://" amqp_authority [ "/" vhost ] [ "?" query ]
     * amqp_authority = [ amqp_userinfo "@" ] host [ ":" port ]
     * amqp_userinfo  = username [ ":" password ]
     * username       = *( unreserved / pct-encoded / sub-delims )
     * password       = *( unreserved / pct-encoded / sub-delims )
     * vhost          = segment
     * ```
     *
     * If `port` is omitted, defaults are:
     *
     * + 5672 for amqp
     * + 5671 for amqps (secured connection)
     *
     * @see https://www.rabbitmq.com/docs/uri-spec
     */
    public static function fromDsn(string $dsn): AmqpBroker
    {
        $params = parse_url($dsn);
        $pathParts = isset($params['path']) ? explode('/', trim($params['path'], '/')) : [];

        return new AmqpBroker(
            host: $params['host'] ?? 'localhost',
            port: $params['port'] ?? (str_starts_with($dsn, 'amqps://') ? 5671 : 5672),
            user: rawurldecode($params['user'] ?? ''),
            password: rawurldecode($params['pass'] ?? ''),
            vhost: rawurldecode($pathParts[0] ?? '/'),
        );
    }
}