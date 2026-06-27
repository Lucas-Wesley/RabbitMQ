<?php

// Exemplo do artigo "Work Queues: distribuindo tarefas entre workers"
// https://lucaswesley.com/artigos/rabbitmq-work-queues
//
// Suba VÁRIOS workers em terminais diferentes para ver a distribuição round-robin.

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

$channel->queue_declare('notas_fiscais', false, true, false, false);

$callback = function (AMQPMessage $mensagem) {
    $tarefa = $mensagem->getBody();
    echo " [.] Gerando PDF de {$tarefa}\n";
    sleep(2); // simula a renderização do PDF
    echo " [x] Concluído {$tarefa}\n";
};

// auto-ack (no_ack = true): mantém o foco na distribuição. Ack manual vem no exemplo 07.
$channel->basic_consume('notas_fiscais', '', false, true, false, false, $callback);

echo " [*] Worker pronto. CTRL+C para sair\n";

while ($channel->is_consuming()) {
    $channel->wait();
}
