<?php

declare(strict_types = 1);

namespace App\Presenters;

trait WebPresenterTrait
{

    /** @var string */
    public $siteName;

    public function beforeRender(): void
    {
        parent::beforeRender();

        $this->template->siteName = $this->siteName;
    }

}
