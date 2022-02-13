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
        $product = new \AmazonPriceWatcher\Amazon('https://www.amazon.es/gp/product/B08MV83J94/');
        $product->scrap();
        $payload = json_encode(['product' => $product ]);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });
};
