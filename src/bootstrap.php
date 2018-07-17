<?php

declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator();

((\getenv('APP_DEBUG') === '1') === true) && $configurator->setDebugMode(true);
$configurator->enableTracy(__DIR__ . '/../var/logs');

(($timeZone = \getenv('TIMEZONE')) !== false) && $configurator->setTimeZone($timeZone);
$configurator->setTempDirectory(__DIR__ . '/../var/temp');
$configurator->addConfig(__DIR__ . '/../config/main.neon');
$container = $configurator->createContainer();

return $container;
