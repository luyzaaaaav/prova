
<?php
require __DIR__ . '/../vendor/autoload.php';

use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// Array de missões espaciais
$missoes = [
    ["id" => 1, "nome" => "Apollo 11", "ano" => 1969, "agencia" => "NASA", "status" => "Concluida"],
    ["id" => 2, "nome" => "Mars Pathfinder", "ano" => 1996, "agencia" => "NASA", "status" => "Concluida"],
    ["id" => 3, "nome" => "Voyager 1", "ano" => 1977, "agencia" => "NASA", "status" => "Concluida"],
    ["id" => 4, "nome" => "Artemis I", "ano" => 2022, "agencia" => "NASA", "status" => "Concluida"],
    ["id" => 5, "nome" => "Starship", "ano" => 2023, "agencia" => "SpaceX", "status" => "Em andamento"],
    ["id" => 6, "nome" => "Juno", "ano" => 2011, "agencia" => "NASA", "status" => "Em andamento"],
    ["id" => 7, "nome" => "Perseverance", "ano" => 2020, "agencia" => "NASA", "status" => "Em andamento"],
    ["id" => 8, "nome" => "Lutadora Voadora", "ano" => 1967, "agencia" => "SpaceX", "status" => "Em andamento"],
    ["id" => 9, "nome" => "Voyager 2", "ano" => 1977, "agencia" => "NASA", "status" => "Concluida"],
    ["id" => 10, "nome" => "Apollo 13", "ano" => 1970, "agencia" => "NASA", "status" => "Concluida"]
];

// Função auxiliar para responder em JSON
function responderJson($response, $dados, $status = 200)
{
    $response->getBody()->write(json_encode(
        $dados,
        JSON_UNESCAPED_UNICODE
    ));

    return $response
        ->withHeader('Content-Type', 'application/json')
        ->withStatus($status);
}

// Página inicial da API
$app->get('/', function ($request, $response) {
    return responderJson($response, [
        'api' => 'Missões Espaciais',
        'rotas' => [
            '/',
            '/status',
            '/status/xml',
            '/status/plain',
            '/missoes',
            '/missoes/{id}'
        ]
    ]);
});

// Verifica o status da API em JSON
$app->get('/status', function ($request, $response) {
    return responderJson($response, ['status' => 'ok']);
});

// Verifica o status em XML
$app->get('/status/xml', function ($request, $response) {
    $response->getBody()->write('<status>ok</status>');

    return $response
        ->withHeader('Content-Type', 'application/xml')
        ->withStatus(200);
});

// Verifica o status em texto puro
$app->get('/status/plain', function ($request, $response) {
    $response->getBody()->write('Status: ok');

    return $response
        ->withHeader('Content-Type', 'text/plain')
        ->withStatus(200);
});

// Lista todas as missões ou filtra pelo nome
$app->get('/missoes', function ($request, $response) use (&$missoes) {
    $queryParams = $request->getQueryParams();
    $nome = $queryParams['nome'] ?? null;

    if ($nome !== null && $nome !== '') {
        $filtrados = array_values(array_filter(
            $missoes,
            fn($item) => str_contains(
                mb_strtolower($item['nome']),
                mb_strtolower($nome)
            )
        ));

        return responderJson($response, $filtrados);
    }

    return responderJson($response, $missoes);
});

// Busca uma missão pelo ID
$app->get('/missoes/{id}', function ($request, $response, $args) use (&$missoes) {
    $id = (int) $args['id'];

    foreach ($missoes as $missao) {
        if ($missao['id'] === $id) {
            return responderJson($response, $missao);
        }
    }

    return responderJson(
        $response,
        ['erro' => 'Missão não encontrada'],
        404
    );
});

// Cadastra uma nova missão
$app->post('/missoes', function ($request, $response) use (&$missoes) {
    $dados = $request->getParsedBody();

    if (
        !is_array($dados) ||
        !isset($dados['nome']) ||
        trim((string) $dados['nome']) === ''
    ) {
        return responderJson(
            $response,
            ['erro' => 'O nome da missão é obrigatório'],
            400
        );
    }

    $novaMissao = [
        'id' => max(array_column($missoes, 'id')) + 1,
        'nome' => $dados['nome'],
        'ano' => $dados['ano'] ?? null,
        'agencia' => $dados['agencia'] ?? null,
        'status' => $dados['status'] ?? null
    ];

    $missoes[] = $novaMissao;

    return responderJson($response, $novaMissao, 201);
});

// Atualiza uma missão pelo ID
$app->put('/missoes/{id}', function ($request, $response, $args) use (&$missoes) {
    $id = (int) $args['id'];
    $dados = $request->getParsedBody();

    foreach ($missoes as &$missao) {
        if ($missao['id'] === $id) {
            if (!is_array($dados) || empty($dados)) {
                unset($missao);

                return responderJson(
                    $response,
                    ['erro' => 'Informe os dados para atualizar'],
                    400
                );
            }

            // Atualiza somente os campos enviados
            foreach (['nome', 'ano', 'agencia', 'status'] as $campo) {
                if (array_key_exists($campo, $dados)) {
                    $missao[$campo] = $dados[$campo];
                }
            }

            $atualizada = $missao;
            unset($missao);

            return responderJson($response, $atualizada);
        }
    }

    unset($missao);

    return responderJson(
        $response,
        ['erro' => 'Missão não encontrada'],
        404
    );
});

// Exclui uma missão pelo ID
$app->delete('/missoes/{id}', function ($request, $response, $args) use (&$missoes) {
    $id = (int) $args['id'];

    foreach ($missoes as $indice => $missao) {
        if ($missao['id'] === $id) {
            array_splice($missoes, $indice, 1);

            return $response->withStatus(204);
        }
    }

    return responderJson(
        $response,
        ['erro' => 'Missão não encontrada'],
        404
    );
});

return $app;
