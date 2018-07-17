<?php

declare(strict_types = 1);

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator();

$debugMode = (bool) \getenv('APP_DEBUG');
if ($debugMode === true) {
    \umask(0000);
    $configurator->setDebugMode(true);
}

$configurator->enableTracy(__DIR__ . '/../var/logs');

$timeZone = \getenv('TIMEZONE');
if ($timeZone !== false) {
    $configurator->setTimeZone($timeZone);
}

$configurator->setTempDirectory(__DIR__ . '/../var/temp');
$configurator->addConfig(__DIR__ . '/../config/main.neon');
$container = $configurator->createContainer();

return $container;
