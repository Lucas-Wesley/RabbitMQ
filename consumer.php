<?php

use Lucaswesley\Rabbitmq\RabbitMQ\Consumer;

require __DIR__ . '/vendor/autoload.php';

$queue = $argv[1] ?? 'default';

echo "Aguardando mensagens na fila [{$queue}]...\n";

(new Consumer())->consume($queue, function (array $data, array $dadosExtras) use ($queue) {

    echo '[' . date('H:i:s') . "] [{$queue}] " . json_encode($data) . " - " . json_encode($dadosExtras) . "\n";
});
