<?php

namespace Lucaswesley\Rabbitmq\RabbitMQ;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class Connection
{
    private static ?AMQPStreamConnection $instance = null;

    public static function get(): AMQPStreamConnection
    {
        if (self::$instance === null || !self::$instance->isConnected()) {
            self::$instance = new AMQPStreamConnection(
                host: getenv('RABBITMQ_HOST') ?: 'rabbitmq',
                port: getenv('RABBITMQ_PORT') ?: 5672,
                user: getenv('RABBITMQ_USER') ?: 'admin',
                password: getenv('RABBITMQ_PASS') ?: 'admin',
            );
        }

        return self::$instance;
    }
}
