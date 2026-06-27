# 04 — Hello World

Primeiro producer e consumer em PHP: connection, channel, `queue_declare`, `basic_publish` e o callback do consumer.

**Teoria:** [Hello World em PHP com php-amqplib](https://lucaswesley.com/artigos/rabbitmq-hello-world-php)

## Como rodar

Com o broker no ar (`docker compose up -d` na raiz do projeto), abra dois terminais.

Consumer (fica de plantão):

```bash
docker compose exec php php examples/04-hello-world/consumer.php
```

Producer (publica e encerra):

```bash
docker compose exec php php examples/04-hello-world/producer.php
```

A mensagem publicada aparece no terminal do consumer. Se rodar o producer algumas vezes antes de subir o consumer, veja as mensagens acumuladas na fila `pedidos` em http://localhost:15672 (admin / admin).

> A fila é declarada como **durável** (`durable = true`). O RabbitMQ 4.x não permite mais filas transientes (não duráveis) por padrão, então durável é o piso. Persistência de mensagem e reconhecimento manual vêm no exemplo `07`.

Para resetar a fila: `docker compose exec rabbitmq rabbitmqctl delete_queue pedidos`.
