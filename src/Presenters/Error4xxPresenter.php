<?php

declare(strict_types = 1);

namespace App\Presenters;

use Nette\Application;

class Error4xxPresenter extends WebPresenter
{

    public function startup(): void
    {
        parent::startup();

        $request = $this->getRequest();
        if ($request !== null && !$request->isMethod(Application\Request::FORWARD)) {
            $this->error();
        }
    }

    public function renderDefault(Application\BadRequestException $exception): void
    {
        $file = \sprintf(__DIR__ . '/../templates/Error/%s.latte', $exception->getCode()); /** @var string $file */
        $file = \is_file($file) ? $file : __DIR__ . '/../templates/Error/4xx.latte';

        $this->template->setFile($file);
    }

}
