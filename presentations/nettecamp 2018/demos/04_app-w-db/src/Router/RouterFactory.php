<?php

declare(strict_types = 1);

namespace App\Router;

use Nette;
use Nette\Application;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{

    use Nette\StaticClass;

    public static function createRouter(): Application\IRouter
    {
        $router = new RouteList();
        $router[] = new Route('api/<presenter>[/<id>]', [
            'presenter' => [
                Route::FILTER_IN => function (string $name) {
                    $name = \sprintf('%sRest', \ucfirst($name));

                    return $name;
                },
                Route::FILTER_OUT => function (string $name) {
                    return $name;
                },
            ],
        ]);
        $router[] = new Route('<presenter>/<action>[/<id>]', 'Homepage:default');

        return $router;
    }

}
