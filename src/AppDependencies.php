<?php

declare(strict_types=1);

use App\Application\Settings\SettingsInterface;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use Slim\Views\Twig;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        "settings" => require __DIR__ . '/AppSettings.php',
        "view" => function (ContainerInterface $c) {
            $settings = $c->get("settings");
            return Twig::create(
                $settings['twig']['templatePath'],
                [ 'cache' => $settings['twig']['enableCache'] ? $settings['twig']['cachePath']: false ]
            );
        },
        "mainLogger" => function(ContainerInterface $c) {
            $settings = $c->get("settings");
            $logger = new \Monolog\Logger($settings['mainLogger']['name']);
            //$logger->pushProcessor(new \Monolog\Processor\UidProcessor());
            $handler = new \Monolog\Handler\RotatingFileHandler($settings['mainLogger']['path'], 0, $settings['mainLogger']['level']);
            $handler->setFilenameFormat('{date}/{filename}', \Monolog\Handler\RotatingFileHandler::FILE_PER_DAY);
            $logger->pushHandler($handler);
            return ($logger);
        }
    ]);
};