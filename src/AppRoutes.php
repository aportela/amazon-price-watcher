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
            $product = null;
            $notFound = false;
            $invalidURL = false;
            try {
                $product = new \AmazonPriceWatcher\Amazon($request->getParsedBody()['url'] ?? '');
            } catch (\InvalidArgumentException $e) {
                $invalidURL = true;
            }
            if ($product && ! $invalidURL) {
                try {
                    $product->scrap();
                } catch (\Throwable $e) {
                    $product = null;
                    if ($e->getMessage() == "404") {
                        $notFound = true;
                    }
                }
                $payload = json_encode(['product' => $product ]);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus($notFound ? 404: 200);
            } else {
                $payload = json_encode(['error' => 'Invalid url param' ]);
                $response->getBody()->write($payload);
                return $response
                    ->withHeader('Content-Type', 'application/json')
                    ->withStatus(400);
            }
        } else {
            $payload = json_encode(['error' => 'Missing url param' ]);
            $response->getBody()->write($payload);
            return $response
                ->withHeader('Content-Type', 'application/json')
                ->withStatus(400);
        }
    });

    $app->get('/api/search', function (Request $request, Response $response, array $args) {
        $data = array(
            "groups" => array(
                array(
                    "name" => "Computers",
                    "price" => 1000.35,
                    "previousPrice" => 999.12,
                    "items" => array(
                        array(
                            "url" => 'https://www.amazon.es/Intel-Core-i9-10850K-Procesador-sobremesa/dp/B08CGT7T32/',
                            "asin" => 'B08CGT7T32',
                            "name" => "Intel Core i9-10850K - Procesador de sobremesa (10 núcleos hasta 5,2 GHz, LGA1200 Intel 400 chipset), 125 W (99A6W4)",
                            "price" => 505.49,
                            "previousPrice" => 600.95,
                            "currency" => "€"
                        ),
                        array(
                            "url" => 'https://www.amazon.es/ASUS-ROG-Strix-Z590-F-Gaming/dp/B08CDTJ1M8/',
                            "asin" => 'B08CDTJ1M8',
                            "name" => 'ASUS ROG STRIX Z590-F GAMING WIFI - Placa base (Intel Z590 LGA 1200 ATX con VRM de 16 fases, PCIe 4.0, WIFI 6E, 2 Intel 2.5 GB Ethernet, 4 M.2 con disipadores, USB 3.2 Gen. 2, SATA y AURA Sync)',
                            "price" => 316.94,
                            "previousPrice" => 288.35,
                            "currency" => "€"
                        ),
                        array(
                            "url" => 'https://www.amazon.es/Corsair-Vengeance-LPX-CMK32GX4M2E3200C16-m%C3%B3dulo/dp/B07RW6Z692/',
                            "asin" => 'B07RW6Z692',
                            "name" => 'Corsair Vengeance LPX CMK32GX4M2E3200C16 módulo de Memoria 32 GB DDR4 3200 MHz',
                            "price" => 144.99,
                            "previousPrice" => 143.73,
                            "currency" => "€"
                        )
                    )
                )
            ),
            "items" => array(
                array(
                    "url" => 'https://www.amazon.es/gp/product/B00UC14EDQ/',
                    "asin" => 'B00UC14EDQ',
                    "name" => 'Fruit of the Loom Heavy Cotton Tee Shirt 3 Pack, Camiseta de Manga Corta Para Hombre',
                    "price" => 7.91,
                    "previousPrice" => 11.99,
                    "currency" => "€"
                ),
                array(
                    "url" => 'https://www.amazon.es/gp/product/B0799QWLWY/',
                    "asin" => 'B0799QWLWY',
                    "name" => 'M MAGEFESA Colombia - La cafetera Colombia está Fabricada en Aluminio Extra Grueso. Pomo y Mangos ergonómicos de bakelita Toque Frio (Negro, 12 Tazas), 8429113134167',
                    "price" => 28.07,
                    "previousPrice" => 21.99,
                    "currency" => "€"
                ),
                array(
                    "url" => 'https://www.amazon.es/BRA-PRIOR-extra%C3%ADbles-inducci%C3%B3n-horno-Libre/dp/B00YF4TQLQ/',
                    "asin" => 'B00YF4TQLQ',
                    "name" => 'BRA PRIOR - Cacerola baja con tapa de cristal y asas de silicona, apta para todo tipo de cocinas incluida inducción y horno, 32 cm',
                    "price" => 36.99,
                    "previousPrice" => 34.44,
                    "currency" => "€"
                )
            )
        );
        $payload = json_encode($data);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(200);
    });
};
