# RabbitMQ na prática (PHP)

Código rodável da série de artigos sobre RabbitMQ. Cada exemplo aqui corresponde a um artigo, com a teoria do lado do blog e o código do lado do repositório. A ideia é ler o artigo e rodar o exemplo lado a lado.

- **Teoria:** série RabbitMQ em [lucaswesley.com](https://lucaswesley.com/artigos)
- **Prática:** este repositório, em `examples/`
- **Projeto final:** a abstração em `src/` (Connection, Topology, Producer, Consumer) é a versão polida que junta tudo.
- **Dashboard web:** em http://localhost:8080 dá para publicar, consumir uma mensagem por vez e ver as filas ao vivo, sem terminal.

## Pré-requisitos

Só Docker. O `docker-compose.yml` sobe o broker (RabbitMQ com Management UI), o PHP e o nginx.

## Começando

```bash
docker compose up -d
```

Isso deixa no ar:

- **RabbitMQ Management UI:** http://localhost:15672 (usuário `admin`, senha `admin`)
- **App / dashboard:** http://localhost:8080

Os exemplos de linha de comando rodam dentro do container PHP. Cada pasta em `examples/` tem um README com os comandos e o link para o artigo correspondente. Em geral:

```bash
# consumer/worker (fica de plantão, use um terminal por worker)
docker compose exec php php examples/06-work-queues/worker.php

# producer (publica e encerra)
docker compose exec php php examples/06-work-queues/producer.php
```

Para resetar uma fila entre exemplos:

```bash
docker compose exec rabbitmq rabbitmqctl delete_queue <nome-da-fila>
```

## Mapa: artigo ↔ exemplo

| #  | Artigo (teoria) | Código (prática) |
|----|-----------------|------------------|
| 1  | [O problema que as filas resolvem](https://lucaswesley.com/artigos/rabbitmq-o-problema-que-filas-resolvem) | teórico |
| 2  | [Broker, fila e log (Kafka e SQS)](https://lucaswesley.com/artigos/rabbitmq-broker-fila-e-log) | teórico |
| 3  | [O modelo mental do RabbitMQ](https://lucaswesley.com/artigos/rabbitmq-modelo-mental) | teórico |
| 4  | [Hello World em PHP](https://lucaswesley.com/artigos/rabbitmq-hello-world-php) | [`examples/04-hello-world`](examples/04-hello-world) |
| 5  | [Anatomia da mensagem e asserção de filas](https://lucaswesley.com/artigos/rabbitmq-anatomia-mensagem-e-asercao-de-filas) | [`examples/05-anatomia-mensagem`](examples/05-anatomia-mensagem) |
| 6  | [Work Queues](https://lucaswesley.com/artigos/rabbitmq-work-queues) | [`examples/06-work-queues`](examples/06-work-queues) |
| 7  | [Acknowledgements e durabilidade](https://lucaswesley.com/artigos/rabbitmq-ack-e-durabilidade) | [`examples/07-ack-durabilidade`](examples/07-ack-durabilidade) |
| 8  | [Exchange fanout (pub/sub)](https://lucaswesley.com/artigos/rabbitmq-exchange-fanout) | [`examples/08-exchange-fanout`](examples/08-exchange-fanout) |
| 9  | Exchange direct | em breve |
| 10 | Exchange topic | em breve |
| 11 | Exchange headers e default | em breve |
| 12 | Publisher confirms | em breve |
| 13 | Dead Letter Exchange | em breve |
| 14 | TTL, retry e backoff | em breve |
| 15 | Mensagens não roteadas | em breve |
| 16 | Quorum vs classic queues | em breve |
| 17 | Prefetch e throughput | em breve |
| 18 | RPC | em breve |
| 19 | Idempotência e at-least-once | em breve |
| 20 | Priority e lazy queues | em breve |
| 21 | RabbitMQ Streams | em breve |
| 22 | Observabilidade | em breve |
| 23 | Erros comuns em produção | em breve |
| 24 | Projeto: pipeline de pedidos | `src/` (abstração final) |

## Estrutura

```
examples/        # um exemplo cru e mínimo por artigo (fiel ao snippet do texto)
src/             # abstração polida (projeto final) + dashboard
  RabbitMQ/      #   Connection, Topology, Producer, Consumer
  bootstrap.php  #   app Slim: dashboard + API (publish, consume one, filas)
  views/         #   página HTML do dashboard
index.php        # entrypoint web
docker-compose.yml
```
