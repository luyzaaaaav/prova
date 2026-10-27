<?php
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;
class AtividadeAvaliativaTest extends TestCase
{
public function testGetStatus(): void
{
$app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
$request = (new ServerRequestFactory())->createServerRequest('GET', '/status');
$response = $app->handle($request);
$this->assertEquals(200, $response->getStatusCode());
$corpo = json_decode((string) $response->getBody(), true);
$this->assertEquals('ok', $corpo['status']);
}
 
public function testGetStatusXml(): void
{
$app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
$request = (new ServerRequestFactory())->createServerRequest('GET', '/status/xml');
$response = $app->handle($request);
$this->assertEquals(200, $response->getStatusCode());
$this->assertEquals('application/xml', $response->getHeaderLine('Content-Type'));
$this->assertEquals('<status>ok</status>', (string) $response->getBody());
}
 
public function testGetStatusPlain(): void
{
$app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
$request = (new ServerRequestFactory())->createServerRequest('GET', '/status/plain');
$response = $app->handle($request);
$this->assertEquals(200, $response->getStatusCode());
$this->assertEquals('text/plain', $response->getHeaderLine('Content-Type'));
$this->assertEquals('Status: ok', (string) $response->getBody());
}
 
public function testGetInicio(): void
{
$app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
$request = (new ServerRequestFactory())->createServerRequest('GET', '/');
$response = $app->handle($request);
$this->assertEquals(200, $response->getStatusCode());
$corpo = json_decode((string) $response->getBody(), true);
$this->assertEquals('Missões Espaciais', $corpo['api']);
$this->assertContains('/missoes', $corpo['rotas']);
}
 
public function testGetMissoes(): void
{
$app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
$request = (new ServerRequestFactory())->createServerRequest('GET', '/missoes');
$response = $app->handle($request);
$this->assertEquals(200, $response->getStatusCode());
$corpo = json_decode((string) $response->getBody(), true);
$this->assertCount(10, $corpo);
$this->assertEquals('Apollo 11', $corpo[0]['nome']);
}
 
public function testGetMissaoPorId(): void
{
$app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
$request = (new ServerRequestFactory())->createServerRequest('GET', '/missoes/2');
$response = $app->handle($request);
$this->assertEquals(200, $response->getStatusCode());
$corpo = json_decode((string) $response->getBody(), true);
$this->assertEquals('Mars Pathfinder', $corpo['nome']);
}
 
public function testGetMissaoPorIdInexistente(): void
{
$app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
$request = (new ServerRequestFactory())->createServerRequest('GET', '/missoes/999');
$response = $app->handle($request);
$this->assertEquals(404, $response->getStatusCode());
$corpo = json_decode((string) $response->getBody(), true);
$this->assertEquals('Missão não encontrada', $corpo['erro']);
}
 
public function testPostCriaMissao(): void
{
$app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
$request = (new ServerRequestFactory())
->createServerRequest('POST', '/missoes')
->withHeader('Content-Type', 'application/json');
$request->getBody()->write(json_encode(['nome' => 'Nova missão', 'ano' => 2025, 'agencia' => 'NASA', 'status' => 'Planejada']));
$request->getBody()->rewind();
$response = $app->handle($request);
$this->assertEquals(201, $response->getStatusCode());
$corpo = json_decode((string) $response->getBody(), true);
$this->assertEquals(11, $corpo['id']);
$this->assertEquals('Nova missão', $corpo['nome']);
}
 
public function testPostCriaMissaoComErro(): void
{
$app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
$request = (new ServerRequestFactory())
->createServerRequest('POST', '/missoes')
->withHeader('Content-Type', 'application/json');
$request->getBody()->write(json_encode([]));
$request->getBody()->rewind();
$response = $app->handle($request);
$this->assertEquals(400, $response->getStatusCode());
$corpo = json_decode((string) $response->getBody(), true);
$this->assertEquals('O nome da missão é obrigatório', $corpo['erro']);
}
 
public function testPutMissaoPorId(): void
{
$app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
$request = (new ServerRequestFactory())
->createServerRequest('PUT', '/missoes/1')
->withHeader('Content-Type', 'application/json');
$request->getBody()->write(json_encode(['nome' => 'Apollo atualizado']));
$request->getBody()->rewind();
$response = $app->handle($request);
$this->assertEquals(200, $response->getStatusCode());
$corpo = json_decode((string) $response->getBody(), true);
$this->assertEquals('Apollo atualizado', $corpo['nome']);
}
 
public function testPutMissaoPorIdInexistente(): void
{
$app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
$request = (new ServerRequestFactory())
->createServerRequest('PUT', '/missoes/999')
->withHeader('Content-Type', 'application/json');
$request->getBody()->write(json_encode(['nome' => 'Missão atualizada']));
$request->getBody()->rewind();
$response = $app->handle($request);
$this->assertEquals(404, $response->getStatusCode());
$corpo = json_decode((string) $response->getBody(), true);
$this->assertEquals('Missão não encontrada', $corpo['erro']);
}
 
public function testDeleteMissaoPorId(): void
{
$app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
$request = (new ServerRequestFactory())->createServerRequest('DELETE', '/missoes/1');
$response = $app->handle($request);
$this->assertEquals(204, $response->getStatusCode());
}
 
public function testDeleteMissaoPorIdInexistente(): void
{
$app = require __DIR__ . '/../src/app.php'; // retorna $app sem chamar ->run()
$request = (new ServerRequestFactory())->createServerRequest('DELETE', '/missoes/999');
$response = $app->handle($request);
$this->assertEquals(404, $response->getStatusCode());
$corpo = json_decode((string) $response->getBody(), true);
$this->assertEquals('Missão não encontrada', $corpo['erro']);
}
}