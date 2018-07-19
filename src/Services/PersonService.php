<?php

declare(strict_types = 1);

namespace App\Services;

use DateTimeImmutable;
use Nette;
use Nette\Application;
use Nette\Database;
use Nette\Http\IResponse;
use Nette\Utils;
use Ramsey\Uuid;

class PersonService
{

    use Nette\SmartObject;

    /** @var Database\Connection */
    private $database;

    public function __construct(Database\Connection $database)
    {
        $this->database = $database;
    }

    public function fetchAll(?array $order = null): Database\ResultSet
    {
        if ($order !== null) {
            $order = \array_map(function (string $direction): bool {
                switch ($direction) {
                    case 'asc':
                    case 'ASC':
                        return true;

                    case 'desc':
                    case 'DESC':
                        return false;

                    default:
                        return (bool) $direction;
                }
            }, $order);
            $results = $this->database->query('SELECT * FROM person ORDER BY', $order);

        } else {
            $results = $this->database->query('SELECT * FROM person');
        }

        return $results;
    }

    public function find(string $pk): ?Database\Row
    {
        $result = $this->database->fetch('SELECT * FROM person WHERE uuid = ?', $pk);
        if (!($result instanceof Database\IRow)) {
            $result = null;
        }

        return $result;
    }

    public function createOne(Utils\ArrayHash $data): Uuid\UuidInterface
    {
        if (empty($data['name'])) {
            throw new Application\BadRequestException(
                'Provided data doesn\'t contain required props',
                IResponse::S412_PRECONDITION_FAILED
            );
        }

        $now = new DateTimeImmutable();
        $pk = Uuid\Uuid::uuid4();
        $rowCount = $this->database->query('INSERT INTO person ?', [
            'uuid' => $pk,
            'name' => $data['name'],
            'created_time' => $now,
        ])->getRowCount();

        if ($rowCount !== 1) {
            throw new Nette\IOException('Inserting database row failed', IResponse::S500_INTERNAL_SERVER_ERROR);
        }

        return $pk;
    }

    public function updateOne(Database\Row $person, Utils\ArrayHash $data): ?Database\Row
    {
        if (empty($data['name'])) {
            throw new Application\BadRequestException(
                'Provided data doesn\'t contain required props',
                IResponse::S412_PRECONDITION_FAILED
            );
        }
        if ($data['name'] === $person->name) {
            return null;
        }

        $now = new DateTimeImmutable();
        $name = $data['name'];
        $affectedRowsCount = $this->database->query('UPDATE person SET', [
            'name' => $name,
            'updated_time' => $now,
        ], 'WHERE uuid = ?', $person->uuid)->getRowCount();

        if ($affectedRowsCount !== 1) {
            throw new Nette\IOException('Updating database row failed', IResponse::S500_INTERNAL_SERVER_ERROR);
        }

        $person->name = $name;

        return $person;
    }

}
