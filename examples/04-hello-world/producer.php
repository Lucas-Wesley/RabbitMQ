<?php

// Exemplo do artigo "Hello World em PHP com php-amqplib"
// https://lucaswesley.com/artigos/rabbitmq-hello-world-php

require __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// No artigo a conexão aparece com localhost/app/secret (caminho standalone via "docker run").
// Aqui, rodando via "docker compose", o host é "rabbitmq" e o usuário "admin", lidos do ambiente.
$connection = new AMQPStreamConnection(
    getenv('RABBITMQ_HOST') ?: 'rabbitmq',
    (int) (getenv('RABBITMQ_PORT') ?: 5672),
    getenv('RABBITMQ_USER') ?: 'admin',
    getenv('RABBITMQ_PASS') ?: 'admin',
);
$channel = $connection->channel();

// RabbitMQ 4.x não permite filas transientes (não duráveis) por padrão: durable = true
$channel->queue_declare('pedidos', false, true, false, false);

$mensagem = new AMQPMessage('pedido 4821 confirmado');
$channel->basic_publish($mensagem, '', 'pedidos');

echo " [x] Mensagem publicada\n";

$channel->close();
$connection->close();
