<?php

// Exemplo do artigo "Acknowledgements e durabilidade"
// https://lucaswesley.com/artigos/rabbitmq-ack-e-durabilidade

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

$channel->queue_declare('notas_fiscais', false, true, false, false); // durable

// delivery_mode persistente: grava a mensagem em disco
$mensagem = new AMQPMessage('gerar-nota:4821', [
    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
]);
$channel->basic_publish($mensagem, '', 'notas_fiscais');

echo " [x] Mensagem persistente publicada em fila durável\n";

$channel->close();
$connection->close();
