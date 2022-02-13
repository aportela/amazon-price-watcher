<?php

declare(strict_types=1);

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Routing\RouteCollectorProxy;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function ($app) {

    // ruta para generar el archivo principal (index)
    $app->get('/', function (Request $request, Response $response, $args) {
        return $this->get('view')->render($response, 'index-webpack.twig', [
            'initialState' => json_encode(
                array(
                    'debug' => $this->get('settings')['debug'],
                    'environment' => $this->get('settings')['environment']
                )
            ),
        ]);
    });


    $app->post('/api/scrap', function (Request $request, Response $response, array $args) {
        $url = $request->getParsedBody()['url'] ?? '';
        if (! empty($url)) {
            $product = new \AmazonPriceWatcher\Amazon($request->getParsedBody()['url'] ?? '');
            $product->scrap();
            $payload = json_encode(['product' => $product ]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } else {
            $payload = json_encode(['error' => 'Missing url param' ]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    });
};
