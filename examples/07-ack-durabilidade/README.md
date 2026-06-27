# 07 — Acknowledgements e durabilidade

Reconhecimento manual (sobrevive à queda do consumer) e fila durável + mensagem persistente (sobrevive à queda do broker).

**Teoria:** [Acknowledgements e durabilidade](https://lucaswesley.com/artigos/rabbitmq-ack-e-durabilidade)

## Como rodar

```bash
# worker com ack manual
docker compose exec php php examples/07-ack-durabilidade/worker.php

# producer: mensagem persistente em fila durável
docker compose exec php php examples/07-ack-durabilidade/producer.php
```

## Experimentos

**Queda do consumer:** publique uma mensagem, e mate o worker (CTRL+C) durante o `sleep(2)`, antes do ack. Suba o worker de novo: a mensagem é reentregue, porque não foi confirmada.

**Queda do broker:** publique a mensagem, e reinicie o broker com `docker compose restart rabbitmq`. Como a fila é durável e a mensagem é persistente, ela continua lá quando o broker volta. Troque `delivery_mode` para não persistente (ou a fila para não durável) e repita: a mensagem some.

> O `nack(true)` devolve a mensagem para a fila. Para falha **permanente** (poison message), isso vira loop infinito — o conserto é Dead Letter Exchange + retry, nos exemplos `13` e `14`.
