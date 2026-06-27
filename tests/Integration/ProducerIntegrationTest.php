<?php
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
use Slim\Psr7\Factory\StreamFactory;

class ProducerIntegrationTest extends TestCase
{
    public function test_publish_enqueues_message(): void
    {
      require __DIR__ . '/../../src/bootstrap.php'; // seu app configurado
      $body = json_encode(['message' => 'teste']);
      $queue = 'pedidos';
      $stream = (new StreamFactory())->createStream($body ?? '');
      $request = (new ServerRequestFactory())
          ->createServerRequest('POST', "/publish/{$queue}")
          ->withHeader('Content-Type', 'application/json')
          ->withBody($stream); // body com o JSON

      $response = $app->handle($request);

      $this->assertEquals(201, $response->getStatusCode());
      $this->assertStringContainsString('published', (string) $response->getBody());
      $this->assertStringContainsString('message', (string) $response->getBody());
    }
}

// object(Slim\Psr7\Stream)#478 (9) {
//   ["stream":protected]=>
//   resource(475) of type (stream)
//   ["meta":protected]=>
//   array(6) {
//     ["wrapper_type"]=>
//     string(3) "PHP"
//     ["stream_type"]=>
//     string(4) "TEMP"
//     ["mode"]=>
//     string(3) "w+b"
//     ["unread_bytes"]=>
//     int(0)
//     ["seekable"]=>
//     bool(true)
//     ["uri"]=>
//     string(10) "php://temp"
//   }
//   ["readable":protected]=>
//   NULL
//   ["writable":protected]=>
//   bool(true)
//   ["seekable":protected]=>
//   NULL
//   ["size":protected]=>
//   NULL
//   ["isPipe":protected]=>
//   NULL
//   ["finished":protected]=>
//   bool(false)
//   ["cache":protected]=>
//   NULL
// }
// .           
