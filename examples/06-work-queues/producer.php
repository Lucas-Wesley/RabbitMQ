<?php

// Exemplo do artigo "Work Queues: distribuindo tarefas entre workers"
// https://lucaswesley.com/artigos/rabbitmq-work-queues

require __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection(
    getenv('RABBITMQ_HOST') ?: 'rabbitmq',
    (int) (getenv('RABBITMQ_PORT') ?: 5672),
    getenv('RABBITMQ_USER') ?: 'admin',
    getenv('RABBITMQ_PASS') ?: 'admin',
);
$channel = $connection->channel();

$channel->queue_declare('notas_fiscais', false, true, false, false);

foreach ([4821, 4822, 4823, 4824, 4825, 4826] as $pedidoId) {
    $mensagem = new AMQPMessage("gerar-nota:{$pedidoId}");
    $channel->basic_publish($mensagem, '', 'notas_fiscais');
    echo " [x] Enfileirado pedido {$pedidoId}\n";
}

$channel->close();
$connection->close();
