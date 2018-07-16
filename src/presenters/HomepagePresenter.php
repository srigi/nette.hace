<?php

namespace App\Presenters;

use Nette\Database;


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

}
