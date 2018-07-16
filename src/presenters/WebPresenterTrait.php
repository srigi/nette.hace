<?php

namespace App\Presenters;


trait WebPresenterTrait
{

    /** @var string */
    public $siteName;


    public function beforeRender()
    {
        parent::beforeRender();

        $this->template->siteName = $this->siteName;
    }
}
