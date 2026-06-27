<?php

// Exemplo do artigo "Hello World em PHP com php-amqplib"
// https://lucaswesley.com/artigos/rabbitmq-hello-world-php

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

// RabbitMQ 4.x não permite filas transientes (não duráveis) por padrão: durable = true
$channel->queue_declare('pedidos', false, true, false, false);

$callback = function (AMQPMessage $mensagem) {
    echo ' [x] Recebido: ', $mensagem->getBody(), "\n";
};

// 4o argumento (no_ack) = true: reconhecimento automático (hello world)
$channel->basic_consume('pedidos', '', false, true, false, false, $callback);

echo " [*] Aguardando mensagens. CTRL+C para sair\n";

while ($channel->is_consuming()) {
    $channel->wait();
}
