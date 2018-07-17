<?php

declare(strict_types = 1);

namespace App\Presenters;

use Nette\Application\UI;

abstract class WebPresenter extends UI\Presenter
{

    use WebPresenterTrait;

}
