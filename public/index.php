<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$missoes = [
    ["id" => 1, "nome" => "Apollo 11", "ano" => 1969, "agencia" => "NASA", "status" => "Concluida"],
    ["id" => 2, "nome" => "Lutadora Voadora", "ano" => 1967, "agencia" => "SpaceX", "status" => "Em andamento"],
    ["id" => 3, "nome" => "Voyager 1", "ano" => 1977, "agencia" => "NASA", "status" => "Concluida"],
    ["id" => 4, "nome" => "Mars Pathfinder", "ano" => 1996, "agencia" => "NASA", "status" => "Concluida"],
    ["id" => 5, "nome" => "Artemis I", "ano" => 2022, "agencia" => "NASA", "status" => "Concluida"],
    ["id" => 6, "nome" => "Starship", "ano" => 2023, "agencia" => "SpaceX", "status" => "Em andamento"],
    ["id" => 7, "nome" => "Juno", "ano" => 2011, "agencia" => "NASA", "status" => "Em andamento"],
    ["id" => 8, "nome" => "Perseverance", "ano" => 2020, "agencia" => "NASA", "status" => "Em andamento"]
];
// sistema para verificar se a publicação local do site está funcionando corretamente
$app->get('/status', function ($request, $response) {
    $response->getBody()->write(json_encode(['status' => 'ok']));
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->get('/missoes/{id}', function ($request, $response, $args) use (&$missoes) {
    $id = (int) $args['id'];
    $missao = current(
        array_filter(
            $missoes,
            fn($p) => $p['id'] === $id
        )
    );

    if (!$missao) {
        $response->getBody()->write(
            json_encode([
                'erro' => 'missao nao encontrada' 
            ])
        );
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(404);
    }
    // missao encontrado
    $response->getBody()->write(
        json_encode($missao)
    );

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(200);
});

$app->post('/missoes', function ($request, $response) use (&$missoes) {
    // Recebe os dados enviados em JSON
    $dados = $request->getParsedBody();
    // Verifica se o nome foi informado
    if (!isset($dados['nome']) || empty($dados['nome'])) {
        $response->getBody()->write(
            json_encode([
                'erro' => 'O nome é obrigatório'
            ])
        );
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400);
    }
    // Cria um novo missao
    $novomissao = [
        'id' => count($missoes) + 1,
        'nome' => $dados['nome'],
        'ano' => $dados['ano'],
        'agencia' => $dados['agencia'],
        'status' => $dados['status']
    ];
    // Adiciona o missao ao array
    $missoes[] = $novomissao;
    // Retorna o missao criado
    $response->getBody()->write(
        json_encode($novomissao)
    );

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus(201);
});

$app->put('/missoes/{id}', function ($request, $response, $args) use (&$missoes) {
    $dados = $request->getParsedBody();

    foreach ($missoes as &$missao) {
        if ($missao['id'] === (int) $args['id']) {
            if ($dados['nome'] == null || $dados['ano'] == null || $dados['agencia'] == null || $dados['status'] == null) {
                $response->getBody()->write(
                    json_encode([
                        'erro' => 'Complete todos os campos.'
                    ])
                );
            } else {
                $missao['nome'] = $dados['nome'];
                $missao['ano'] = $dados['ano'];
                $missao['agencia'] = $dados['agencia'];
                $missao['status'] = $dados['status'];
                $response->getBody()->write(json_encode($missao));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }
        }
    }
    return $response->withStatus(404);
});

$app->get('/missoes', function ($request, $response, $args) use (&$missoes) {
    $queryParams = $request->getQueryParams();
    $nome = $queryParams['nome'] ?? null;
    if ($nome) {
        $filtrados = array_filter($missoes, fn($item) => str_contains(mb_strtolower($item['nome']), mb_strtolower($nome)));
        $response->getBody()->write(json_encode($filtrados));
    } else {
        $response->getBody()->write(json_encode($missoes));
    }
    return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
});

$app->delete('/missoes/{id}', function ($request, $response, $args) use (&$missoes) {
    $missoes = array_values(array_filter($missoes, fn($p) => $p['id'] !== (int) $args['id']));
    return $response->withStatus(204);
});

$app->run();
