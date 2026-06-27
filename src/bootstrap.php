<?php

use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PhpAmqpLib\Message\AMQPMessage;
use Lucaswesley\Rabbitmq\RabbitMQ\Connection;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$json = function (Response $response, array $data, int $status = 200): Response {
    $response->getBody()->write(json_encode($data));
    return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
};

// Dashboard (página HTML)
$app->get('/', function (Request $_, Response $response) {
    $response->getBody()->write(file_get_contents(__DIR__ . '/views/dashboard.html'));
    return $response->withHeader('Content-Type', 'text/html; charset=utf-8');
});

// Estado das filas, via HTTP API do RabbitMQ Management
$app->get('/api/queues', function (Request $_, Response $response) use ($json) {
    $host = getenv('RABBITMQ_HOST') ?: 'rabbitmq';
    $user = getenv('RABBITMQ_USER') ?: 'admin';
    $pass = getenv('RABBITMQ_PASS') ?: 'admin';

    $context = stream_context_create(['http' => [
        'header' => 'Authorization: Basic ' . base64_encode("{$user}:{$pass}"),
        'ignore_errors' => true,
        'timeout' => 3,
    ]]);

    $raw = @file_get_contents("http://{$host}:15672/api/queues", false, $context);
    $queues = $raw ? json_decode($raw, true) : [];

    $out = array_map(fn ($q) => [
        'name' => $q['name'],
        'messages' => $q['messages'] ?? 0,
        'consumers' => $q['consumers'] ?? 0,
        'durable' => $q['durable'] ?? false,
    ], is_array($queues) ? $queues : []);

    return $json($response, $out);
});

// Publica uma mensagem (basic_publish no exchange padrão)
$app->post('/api/publish', function (Request $request, Response $response) use ($json) {
    $body = $request->getParsedBody() ?? [];
    $queue = trim($body['queue'] ?? '') ?: 'playground';
    $message = (string) ($body['message'] ?? '');

    $channel = Connection::get()->channel();
    $channel->queue_declare($queue, false, true, false, false); // durável (RabbitMQ 4.x)
    $channel->basic_publish(
        new AMQPMessage($message, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]),
        '',
        $queue
    );
    $channel->close();

    return $json($response, ['published' => true, 'queue' => $queue, 'message' => $message], 201);
});

// Consome UMA mensagem sob demanda (basic_get + ack), sem ficar de plantão
$app->post('/api/get', function (Request $request, Response $response) use ($json) {
    $body = $request->getParsedBody() ?? [];
    $queue = trim($body['queue'] ?? '') ?: 'playground';

    $channel = Connection::get()->channel();
    $channel->queue_declare($queue, false, true, false, false);
    $message = $channel->basic_get($queue);

    if ($message === null) {
        $channel->close();
        return $json($response, ['empty' => true, 'queue' => $queue]);
    }

    $channel->basic_ack($message->getDeliveryTag());
    $payload = $message->getBody();
    $channel->close();

    return $json($response, ['empty' => false, 'queue' => $queue, 'message' => $payload]);
});

return $app;
