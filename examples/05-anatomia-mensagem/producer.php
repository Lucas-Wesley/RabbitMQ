<?php

// Exemplo do artigo "Anatomia de uma mensagem e asserção de filas"
// https://lucaswesley.com/artigos/rabbitmq-anatomia-mensagem-e-asercao-de-filas

require __DIR__ . '/../../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpAmqpLib\Wire\AMQPTable;

$connection = new AMQPStreamConnection(
    getenv('RABBITMQ_HOST') ?: 'rabbitmq',
    (int) (getenv('RABBITMQ_PORT') ?: 5672),
    getenv('RABBITMQ_USER') ?: 'admin',
    getenv('RABBITMQ_PASS') ?: 'admin',
);
$channel = $connection->channel();

// fila durável (ver erro PRECONDITION_FAILED no README se a fila "pedidos" já existir não durável)
$channel->queue_declare('pedidos', false, true, false, false);

$corpo = json_encode(['pedido_id' => 4821, 'total' => 259.90]);

$mensagem = new AMQPMessage($corpo, [
    'content_type'  => 'application/json',
    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
    'message_id'    => 'pedido-4821-confirmado',
    'timestamp'     => time(),
    'application_headers' => new AMQPTable([
        'origem'    => 'checkout',
        'tentativa' => 1,
    ]),
]);

$channel->basic_publish($mensagem, '', 'pedidos');

echo " [x] Mensagem com properties e headers publicada\n";

$channel->close();
$connection->close();
