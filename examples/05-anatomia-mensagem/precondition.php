<?php

// Dispara PRECONDITION_FAILED de propósito.
// O producer.php declara "pedidos" com auto_delete = false.
// Aqui redeclaro a MESMA fila com auto_delete = true: argumentos divergentes,
// o broker recusa e fecha o channel.
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

// durable igual ao producer (true), mas auto_delete diferente (true em vez de false)
$channel->queue_declare('pedidos', false, true, false, true);

echo "isto não deveria imprimir\n";

$channel->close();
$connection->close();
