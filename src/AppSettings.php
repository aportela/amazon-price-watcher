<?php

declare(strict_types=1);

return [
    'debug'               => true,
    'displayErrorDetails' => true, // Should be set to false in production
    'logError'            => true,
    'logErrorDetails'     => true,
    'environment'         => 'dev',
    'twig' => [
        'templatePath' => __DIR__ . '/../templates',
        'enableCache' => false,
        'cachePath' => __DIR__ . '/../cache'
    ],
    'mainLogger' => [
        'name' => 'main',
        'path' => isset($_ENV['docker']) ? 'php://stdout' : __DIR__ . '/../log/main.log',
        'level' => Monolog\Logger::DEBUG,
    ]
];