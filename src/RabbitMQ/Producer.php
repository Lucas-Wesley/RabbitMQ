<?php

namespace Lucaswesley\Rabbitmq\RabbitMQ;

use PhpAmqpLib\Message\AMQPMessage;

class Producer
{
    public function publish(string $queue, array $data): void
    {
        $connection = Connection::get();
        $channel = $connection->channel();

        Topology::declareQueue($channel, $queue);

        $message = new AMQPMessage(
            json_encode($data),
            ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT, 'content_type' => 'application/json']
        );

        $channel->basic_publish($message, routing_key: $queue);

        $channel->close();
    }
}
