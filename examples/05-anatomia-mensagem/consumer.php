<?php

// Exemplo do artigo "Anatomia de uma mensagem e asserção de filas"
// https://lucaswesley.com/artigos/rabbitmq-anatomia-mensagem-e-asercao-de-filas

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

$channel->queue_declare('pedidos', false, true, false, false);

$callback = function (AMQPMessage $mensagem) {
    $tipo    = $mensagem->get('content_type');
    $id      = $mensagem->get('message_id');
    $headers = $mensagem->get('application_headers')->getNativeData();

    $dados = json_decode($mensagem->getBody(), true);

    echo "id={$id} tipo={$tipo} origem={$headers['origem']}\n";
    echo "pedido {$dados['pedido_id']} total {$dados['total']}\n";
};

$channel->basic_consume('pedidos', '', false, true, false, false, $callback);

echo " [*] Aguardando mensagens. CTRL+C para sair\n";

while ($channel->is_consuming()) {
    $channel->wait();
}
