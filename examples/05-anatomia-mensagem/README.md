# 05 — Anatomia de uma mensagem e asserção de filas

Body, properties e headers de uma mensagem, e os parâmetros de `queue_declare` (durable / exclusive / auto-delete), incluindo o erro `PRECONDITION_FAILED`.

**Teoria:** [Anatomia de uma mensagem e asserção de filas](https://lucaswesley.com/artigos/rabbitmq-anatomia-mensagem-e-asercao-de-filas)

## Como rodar

```bash
# consumer mostrando properties e headers
docker compose exec php php examples/05-anatomia-mensagem/consumer.php

# producer com content_type, delivery_mode, message_id, timestamp e headers
docker compose exec php php examples/05-anatomia-mensagem/producer.php

# inspeção passiva: contagem de mensagens/consumidores sem criar a fila
docker compose exec php php examples/05-anatomia-mensagem/inspect.php
```

## Vendo o erro `PRECONDITION_FAILED`

O `producer.php` cria a fila `pedidos` com `auto_delete = false`. O `precondition.php` redeclara a **mesma** fila com `auto_delete = true`: argumentos divergentes, e o broker recusa, fechando o channel.

```bash
docker compose exec php php examples/05-anatomia-mensagem/producer.php   # cria a fila
docker compose exec php php examples/05-anatomia-mensagem/precondition.php # dispara o erro
```

É a armadilha descrita no artigo: redeclarar uma fila existente com características diferentes não a migra, dá erro. Para resetar:

```bash
docker compose exec rabbitmq rabbitmqctl delete_queue pedidos
```
