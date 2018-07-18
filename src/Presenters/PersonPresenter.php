<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Forms;
use DateTimeImmutable;
use Nette\Application;
use Nette\Application\UI;
use Nette\Database;
use Nette\Utils;
use Ramsey\Uuid;

class PersonPresenter extends WebPresenter
{

    /** @var Database\Connection */
    private $database;

    /** @var Database\Row */
    private $person;

    public function __construct(Database\Connection $database)
    {
        parent::__construct();

        $this->database = $database;
    }

    public function actionEdit(string $uuid): void
    {
        $this->person = $this->database->fetch('SELECT * FROM person WHERE uuid = ?', $uuid);
        if (!($this->person instanceof Database\IRow)) {
            throw new Application\BadRequestException('Page not found');
        }

        $this->template->person = $this->person;
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
        if (!$values['uuid']) {  // creating or updating?
            $this->createPerson($values);
            $this->flashMessage('Person saved successfully', 'info');
        } else {
            $result = $this->updatePerson($values);
            if ($result !== null) {
                $this->flashMessage('Person updated successfully', 'info');
            } else {
                $this->flashMessage('Person wasn\'t updated', 'info');
            }
        }

        $this->redirect('default');
    }

    private function createPerson(Utils\ArrayHash $values): ?Uuid\UuidInterface
    {
        if ($values['name'] !== null) {
            $now = new DateTimeImmutable();
            $pk = Uuid\Uuid::uuid4();
            $rowCount = $this->database->query('INSERT INTO person ?', [
                'uuid' => $pk,
                'name' => $values['name'],
                'created_time' => $now,
            ])->getRowCount();
            if ($rowCount !== 0) {
                return $pk;
            }
        }

        return null;
    }

    private function updatePerson(Utils\ArrayHash $values): ?int
    {
        if ($values['name'] !== null && $values['name'] !== $this->person->name) {
            $now = new DateTimeImmutable();
            $affectedRows = $this->database->query('UPDATE person SET', [
                'name' => $values['name'],
                'updated_time' => $now,
            ], 'WHERE uuid = ?', $values['uuid'])->getRowCount();

            return $affectedRows;
        }

        return null;
    }

}
