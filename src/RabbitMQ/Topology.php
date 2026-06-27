<?php

namespace Lucaswesley\Rabbitmq\RabbitMQ;

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Wire\AMQPTable;

class Topology
{
    /**
     * Declara a fila principal já apontando para a Dead Letter Exchange,
     * além de criar a DLX e a fila de mortos.
     *
     * Precisa ser chamada de forma idêntica pelo Producer e pelo Consumer:
     * os argumentos da fila têm que bater em toda declaração, senão o
     * RabbitMQ recusa com PRECONDITION_FAILED.
     */
    public static function declareQueue(AMQPChannel $channel, string $queue): void
    {
        $deadLetterExchange = $queue . '.dlx';
        $deadLetterQueue = $queue . '.dead';

        // Exchange + fila onde ficam as mensagens que falharam.
        $channel->exchange_declare(
            $deadLetterExchange,
            type: 'fanout',
            passive: false,
            durable: true,
            auto_delete: false,
        );
        $channel->queue_declare(
            $deadLetterQueue,
            passive: false,
            durable: true,
            exclusive: false,
            auto_delete: false,
        );
        $channel->queue_bind($deadLetterQueue, $deadLetterExchange);

        // Fila principal: ao dar nack(requeue: false), a mensagem é
        // redirecionada automaticamente para a DLX em vez de sumir.
        $channel->queue_declare(
            $queue,
            passive: false,
            durable: true,
            exclusive: false,
            auto_delete: false,
            arguments: new AMQPTable([
                'x-dead-letter-exchange' => $deadLetterExchange,
            ]),
        );
    }
}
