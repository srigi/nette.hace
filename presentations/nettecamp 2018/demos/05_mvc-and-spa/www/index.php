<?php

declare(strict_types = 1);

$container = require __DIR__ . '/../src/bootstrap.php';

$container->getByType(Nette\Application\Application::class)
	->run();
