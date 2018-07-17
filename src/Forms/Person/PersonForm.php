<?php

declare(strict_types = 1);

namespace App\Forms\Person;

use Nette\Application\UI;
use Nette\Database;

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
        parent::__construct();

        $this->form = new UI\Form();

        $this->form->addText('name', 'Name')
            ->setRequired();

        $this->form->addHidden('uuid');

        $this->form->addSubmit('save', 'Save');
    }

    public function render(?Database\Row $person = null): void
    {
        if ($person !== null) {
            $this->form->setDefaults([
                'uuid' => $person->uuid,
                'name' => $person->name,
            ]);
        }

        $this->template->render(__DIR__ . '/default.latte');
    }

    protected function createComponentForm(): UI\Form
    {
        $this->form->onSuccess = $this->onSuccess;
        $this->form->onError = $this->onError;

        return $this->form;
    }

}
