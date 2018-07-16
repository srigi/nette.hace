<?php

$container = require __DIR__ . '/../src/bootstrap.php';

$container->getByType(Nette\Application\Application::class)
	->run();
