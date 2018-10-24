<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Forms;
use App\Services;
use Nette\Application;
use Nette\Application\UI;
use Nette\Database;
use Nette\Utils;

class PersonPresenter extends WebPresenter
{

    /** @var Database\Row|null */
    private $person;

    /** @var Services\PersonService */
    private $personService;

    public function __construct(Services\PersonService $personService)
    {
        parent::__construct();

        $this->personService = $personService;
    }

    public function actionEdit(string $uuid): void
    {
        $this->person = $this->personService->find($uuid);
        if ($this->person === null) {
            throw new Application\BadRequestException('Page not found');
        }

        $this->template->person = $this->person;
    }

    public function renderDefault(): void
    {
        $persons = $this->personService->fetchAll(['created_time' => 'DESC']);

        $this->template->persons = $persons;
    }

    protected function createComponentPersonForm(): UI\Control
    {
        $form = new Forms\Person\PersonForm($this->person);
        $form->onSuccess[] = [$this, 'personFormSubmitted'];

        return $form;
    }

    public function personFormSubmitted(UI\Form $form, Utils\ArrayHash $values): void
    {
        if ($this->person === null) {  // creating or updating?
            $this->personService->createOne($values);
            $this->flashMessage('Person saved successfully', 'info');
        } else {
            $result = $this->personService->updateOne($this->person, $values);
            if ($result !== null) {
                $this->flashMessage('Person updated successfully', 'info');
            } else {
                $this->flashMessage('Person wasn\'t updated', 'info');
                $this->redirect('this');
            }
        }

        $this->redirect('default');
    }

}
