<?php

namespace App\Forms\Person;

use Nette\Application\UI;


class PersonForm extends UI\Control
{
    /** @var callable[] */
    public $onSuccess = [];

    /** @var callable[] */
    public $onError = [];

    /** @var UI\Form */
    protected $form;


    public function __construct()
    {
        $this->form = new UI\Form();

        $this->form->addText('name', 'Name')
            ->setRequired();

        $this->form->addHidden('uuid');

        $this->form->addSubmit('save', 'Save');
    }


    public function render($person = null)
    {
        if ($person !== null) {
            $this->form->values = [
                'uuid' => $person->uuid,
                'name' => $person->name,
            ];
        }

        $this->template->render(__DIR__ . '/default.latte');
    }

    protected function createComponentForm()
    {
        $this->form->onSuccess = $this->onSuccess;
        $this->form->onError = $this->onError;

        return $this->form;
    }
}
