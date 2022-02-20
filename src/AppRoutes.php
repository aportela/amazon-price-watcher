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
        $id = $request->getParsedBody()['id'] ?? '';
        if (! empty($url) || ! empty($id)) {
            $product = null;
            $notFound = false;
            $invalidURL = false;
            $errorMessage = null;
            try {
                $item = new \AmazonPriceWatcher\AmazonPriceWatcherItem();
                if (! empty($id)) {
                    $item->id = $id;
                    $item->get();
                } else {
                    $item->setFromURL($url);
                    $item->get();
                }
            } catch (\InvalidArgumentException $e) {
                $invalidURL = true;
            }
            try {
                $item->scrap();
                if (! empty($item->name)) {
                    if (! empty($item->id)) {
                        $item->update();
                    } else {
                        $item->add();
                    }
                }
            } catch (\Throwable $e) {
                if ($e->getMessage() == "404") {
                    $notFound = true;
                } else {
                    $errorMessage = $e->getMessage();
                }
            }
            if (empty($errorMessage)) {
                $payload = json_encode(['product' => $item ]);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($notFound ? 404: 200);
            } else {
                $payload = json_encode(['product' => null, 'error' => $errorMessage ]);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($notFound ? 404: 200);

            }
        } else {
            $payload = json_encode(['error' => 'Missing url param' ]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    });

    $app->post('/api/delete', function (Request $request, Response $response, array $args) {
        $id = $request->getParsedBody()['id'] ?? '';
        if (! empty($id)) {
            \AmazonPriceWatcher\AmazonPriceWatcherItem::delete($id);
            $payload = json_encode(['success' => true ]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(200);
        } else {
            $payload = json_encode(['error' => 'Missing id param' ]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    });

    $app->get('/api/search', function (Request $request, Response $response, array $args) {
        $payload = json_encode(array("items" => \AmazonPriceWatcher\AmazonPriceWatcherItem::search()));
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });
};
