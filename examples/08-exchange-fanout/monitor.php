<?php

// Assinante EFÊMERO: fila temporária, exclusiva e auto-delete.
// Só recebe os pedidos que acontecem enquanto está conectado.
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

// nome vazio: o broker gera um nome único. exclusive + auto_delete: some ao desconectar.
[$fila,,] = $channel->queue_declare('', false, false, true, true);
$channel->queue_bind($fila, 'pedidos_confirmados');

$callback = function (AMQPMessage $mensagem) {
    $pedido = json_decode($mensagem->getBody(), true);
    echo " [painel] +1 venda: pedido {$pedido['pedido_id']}\n";
    $mensagem->ack();
};

$channel->basic_consume($fila, '', false, false, false, false, $callback);

echo " [*] Painel ao vivo na fila temporária [{$fila}]. CTRL+C para sair\n";

while ($channel->is_consuming()) {
    $channel->wait();
}
