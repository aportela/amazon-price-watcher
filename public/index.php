<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$containerBuilder = new ContainerBuilder();

$dependencies = require __DIR__ . '/../src/AppDependencies.php';

$dependencies($containerBuilder);

$container = $containerBuilder->build();

AppFactory::setContainer($container);

$app = AppFactory::create();

(require __DIR__ . '/../src/AppRoutes.php')($app);

$app->run();
