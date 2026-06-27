# 08 — Exchange fanout (publish/subscribe)

Um fanout transmite cada mensagem para todas as filas ligadas a ele. Cada assinante tem a própria fila: duráveis e nomeadas (não perdem nada) ou temporárias e exclusivas (só ao vivo).

**Teoria:** [Exchange fanout: publish/subscribe](https://lucaswesley.com/artigos/rabbitmq-exchange-fanout)

## Como rodar

Suba os assinantes em terminais separados:

```bash
# assinante durável (acumula mesmo offline)
docker compose exec php php examples/08-exchange-fanout/consumer_email.php

# assinante efêmero (fila temporária, só recebe enquanto conectado)
docker compose exec php php examples/08-exchange-fanout/monitor.php
```

Em outro terminal, publique um pedido confirmado:

```bash
docker compose exec php php examples/08-exchange-fanout/producer.php
```

Os dois assinantes recebem a **mesma** mensagem ao mesmo tempo, cada um na sua fila.

## Experimento: durável vs efêmero

Pare os dois assinantes. Publique alguns pedidos com o `producer.php`. Suba só o `consumer_email.php`: ele recebe tudo o que foi publicado enquanto estava fora, porque a fila `email_confirmacao` é durável. O `monitor.php`, ao subir, começa do zero: a fila temporária dele nem existia quando os pedidos foram publicados.
