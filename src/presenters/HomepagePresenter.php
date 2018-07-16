<?php

namespace App\Presenters;

use App\Forms;
use Nette\Application;
use Nette\Application\UI;
use Nette\Database;
use DateTimeImmutable;
use Nette\Utils;
use Ramsey\Uuid\Uuid;


class HomepagePresenter extends WebPresenter
{

    /** @var Database\Connection */
    private $database;


    public function __construct(Database\Connection $database)
    {
        $this->database = $database;
    }


    public function actionEdit($uuid)
    {
        $person = $this->database->fetch('SELECT * FROM person WHERE uuid = ?', $uuid);
        if ($person === false) {
            throw new Application\BadRequestException('Page not found');
        }

        $this->template->person = $person;
    }


    public function renderDefault()
    {
        $persons = $this->database->query('SELECT * FROM person ORDER BY created_time DESC');
        $this->template->persons = $persons;
    }


    protected function createComponentPersonForm()
    {
        $form = new Forms\PersonForm();
        $form->onSuccess[] = [$this, 'personFormSubmitted'];

        return $form;
    }


    public function personFormSubmitted(UI\Form $form, Utils\ArrayHash $values)
    {
        if (empty($values['uuid'])) {
            $this->createPerson($values);
        } else {
            $this->updatePerson($values);
        }

        $this->redirect('default');
    }


    private function createPerson($values)
    {
        $pk = Uuid::uuid4();
        $now = new DateTimeImmutable();
        $this->database->query('INSERT INTO person ?', [
            'uuid' => $pk,
            'name' => $values['name'],
            'created_time' => $now,
        ]);

        $this->flashMessage("Person saved successfully", 'info');
    }


    private function updatePerson($values)
    {
        $person = $this->database->fetch('SELECT * FROM person WHERE uuid = ?', $values['uuid']);
        if ($person === false) {
            throw new Application\BadRequestException('Page not found');
        }

        $result = $this->database->query('UPDATE person SET', [
            'name' => $values['name'],
        ], 'WHERE uuid = ?', $values['uuid']);

        $this->flashMessage("Person updated successfully", 'info');
    }
}
