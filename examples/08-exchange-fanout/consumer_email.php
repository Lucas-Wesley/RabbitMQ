<?php

// Assinante DURÁVEL: fila nomeada que acumula pedidos mesmo offline.
// Artigo: https://lucaswesley.com/artigos/rabbitmq-exchange-fanout

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

$channel->exchange_declare('pedidos_confirmados', 'fanout', false, true, false);
$channel->queue_declare('email_confirmacao', false, true, false, false);
$channel->queue_bind('email_confirmacao', 'pedidos_confirmados');

$callback = function (AMQPMessage $mensagem) {
    $pedido = json_decode($mensagem->getBody(), true);
    echo " [email] enviando confirmação do pedido {$pedido['pedido_id']}\n";
    $mensagem->ack();
};

$channel->basic_consume('email_confirmacao', '', false, false, false, false, $callback);

echo " [*] Serviço de e-mail pronto. CTRL+C para sair\n";

while ($channel->is_consuming()) {
    $channel->wait();
}
