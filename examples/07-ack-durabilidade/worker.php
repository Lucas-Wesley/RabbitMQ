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

$channel->queue_declare('notas_fiscais', false, true, false, false);

$callback = function (AMQPMessage $mensagem) {
    try {
        $tarefa = $mensagem->getBody();
        echo " [.] Gerando PDF de {$tarefa}\n";
        sleep(2); // simula a renderização
        echo " [x] Concluído {$tarefa}\n";
        $mensagem->ack(); // confirma só depois de terminar
    } catch (\Throwable $e) {
        $mensagem->nack(true); // requeue: devolve para a fila (cuidado com poison message)
    }
};

// no_ack = false: reconhecimento manual
$channel->basic_consume('notas_fiscais', '', false, false, false, false, $callback);

echo " [*] Worker (ack manual) pronto. CTRL+C para sair\n";

while ($channel->is_consuming()) {
    $channel->wait();
}
