<?php

// Inspeção passiva: verifica se a fila existe e mostra as contagens,
// sem criar nada. Lança erro se a fila "pedidos" não existir.
// Artigo: https://lucaswesley.com/artigos/rabbitmq-anatomia-mensagem-e-asercao-de-filas

require __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection(
    getenv('RABBITMQ_HOST') ?: 'rabbitmq',
    (int) (getenv('RABBITMQ_PORT') ?: 5672),
    getenv('RABBITMQ_USER') ?: 'admin',
    getenv('RABBITMQ_PASS') ?: 'admin',
);
$channel = $connection->channel();

// passive = true: só verifica, não cria
[$nome, $mensagens, $consumidores] = $channel->queue_declare('pedidos', true);

echo "fila {$nome}: {$mensagens} mensagens acumuladas, {$consumidores} consumidores ativos\n";

$channel->close();
$connection->close();
