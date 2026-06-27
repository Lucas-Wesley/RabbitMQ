# 06 — Work Queues

Vários workers consumindo a mesma fila para dividir trabalho pesado. Distribuição round-robin e consumidores concorrentes.

**Teoria:** [Work Queues: distribuindo tarefas entre workers](https://lucaswesley.com/artigos/rabbitmq-work-queues)

## Como rodar

Suba **dois ou três workers**, cada um num terminal:

```bash
docker compose exec php php examples/06-work-queues/worker.php
```

Em outro terminal, publique as 6 tarefas de uma vez:

```bash
docker compose exec php php examples/06-work-queues/producer.php
```

As tarefas são repartidas em rodízio entre os workers ativos. Cada `sleep(2)` simula a geração de um PDF, então dá para ver o paralelismo: com 2 workers, 6 tarefas saem em ~6s em vez de ~12s.

> Este exemplo usa **auto-ack** de propósito. Se um worker morrer no meio de uma tarefa, ela se perde. O conserto (ack manual) está no exemplo `07`.
