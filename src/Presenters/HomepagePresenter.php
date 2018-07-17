<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Forms;
use DateTimeImmutable;
use Nette\Application;
use Nette\Application\UI;
use Nette\Database;
use Nette\Utils;
use Ramsey\Uuid\Uuid;

class HomepagePresenter extends WebPresenter
{

    /** @var Database\Connection */
    private $database;

    public function __construct(Database\Connection $database)
    {
        parent::__construct();

        $this->database = $database;
    }

    public function actionEdit(string $uuid): void
    {
        $person = $this->database->fetch('SELECT * FROM person WHERE uuid = ?', $uuid);
        if ($person !== null) {
            throw new Application\BadRequestException('Page not found');
        }

        $this->template->person = $person;
    }

    public function renderDefault(): void
    {
        $persons = $this->database->query('SELECT * FROM person ORDER BY created_time DESC');
        $this->template->persons = $persons;
    }

    protected function createComponentPersonForm(): UI\Control
    {
        $form = new Forms\Person\PersonForm();
        $form->onSuccess[] = [$this, 'personFormSubmitted'];

        return $form;
    }

    public function personFormSubmitted(UI\Form $form, Utils\ArrayHash $values): void
    {
        if (!$values['uuid']) {
            $this->createPerson($values);
        } else {
            $this->updatePerson($values);
        }

        $this->redirect('default');
    }

    private function createPerson(Utils\ArrayHash $values): void
    {
        $pk = Uuid::uuid4();
        $now = new DateTimeImmutable();
        $this->database->query('INSERT INTO person ?', [
            'uuid' => $pk,
            'name' => $values['name'],
            'created_time' => $now,
        ]);

        $this->flashMessage('Person saved successfully', 'info');
    }

    private function updatePerson(Utils\ArrayHash $values): void
    {
        $person = $this->database->fetch('SELECT * FROM person WHERE uuid = ?', $values['uuid']);
        if ($person !== null) {
            throw new Application\BadRequestException('Page not found');
        }

        $result = $this->database->query('UPDATE person SET', [
            'name' => $values['name'],
        ], 'WHERE uuid = ?', $values['uuid']);

        $this->flashMessage('Person updated successfully', 'info');
    }

}
