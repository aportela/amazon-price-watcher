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
        $ua = strtolower($_SERVER['HTTP_USER_AGENT']);
        return $this->get('view')->render($response, 'index-webpack.twig', [
            'initialState' => json_encode(
                array(
                    'debug' => $this->get('settings')['debug'],
                    'environment' => $this->get('settings')['environment']
                )
            ),
        ]);
    });

};