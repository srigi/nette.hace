<?php

namespace App\Presenters;

use App\Forms;
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
        $this->database = $database;
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
        $pk = Uuid::uuid4();
        $now = new \DateTimeImmutable();
        $this->database->query('INSERT INTO person ?', [
            'uuid' => $pk,
            'name' => $values['name'],
            'created_time' => $now,
        ]);

        $this->flashMessage("Person save successfully", 'info');
        $this->redirect('default');
    }
}
