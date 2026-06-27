<?php

// Exemplo do artigo "Exchange fanout: publish/subscribe"
// https://lucaswesley.com/artigos/rabbitmq-exchange-fanout

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

$corpo = json_encode(['pedido_id' => 4821, 'total' => 259.90]);
$mensagem = new AMQPMessage($corpo, [
    'content_type'  => 'application/json',
    'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
]);

// fanout ignora a routing key: basta o nome do exchange
$channel->basic_publish($mensagem, 'pedidos_confirmados');

echo " [x] Pedido confirmado publicado no fanout\n";

$channel->close();
$connection->close();
