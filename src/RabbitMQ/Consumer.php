<?php

namespace Lucaswesley\Rabbitmq\RabbitMQ;

class Consumer
{
    public function consume(string $queue, callable $handler): void
    {
        $connection = Connection::get();
        $channel = $connection->channel();

        Topology::declareQueue($channel, $queue);

        // processa uma mensagem por vez — evita sobrecarga
        $channel->basic_qos(prefetch_size: 0, prefetch_count: 1, a_global: false);

        $channel->basic_consume(
            queue: $queue,
            consumer_tag: '',
            no_local: false,
            no_ack: false,
            exclusive: false,
            nowait: false,
            callback: function ($message) use ($handler) {
                try {
                    $contentType = $message->get('content_type'); // 'application/json'
                    $dadosExtras = ['contentType' => $contentType];

                    $data = json_decode($message->body, true, flags: JSON_THROW_ON_ERROR);
                    $handler($data, $dadosExtras);
                    $message->ack();
                } catch (\Throwable $e) {
                    // Não recoloca na fila: evita poison message em loop.
                    // requeue: false -> a mensagem vai para a Dead Letter Queue.
                    $message->nack(requeue: false);

                    error_log("Falha ao processar mensagem: {$e->getMessage()}");
                }
            }
        );

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
    }
}
