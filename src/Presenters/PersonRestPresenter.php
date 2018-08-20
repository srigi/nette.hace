<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Services;
use Nette\Application;
use Nette\Http;
use Nette\Utils;

class PersonRestPresenter extends RestPresenter
{

    /** @var Services\PersonService */
    private $personService;

    public function __construct(Http\Request $request, Services\PersonService $personService)
    {
        parent::__construct($request);

        $this->personService = $personService;
    }

    public function actionGet(array $params): Application\IResponse
    {
        if ($params['id'] !== null) {
            $data = $this->personService->find($params['id']);
            if ($data === null) {
                return $this->sendJson(['status' => 'not found'], Http\IResponse::S404_NOT_FOUND);
            }
        } else {
            $data = $this->personService->fetchAll([
                'created_time' => 'DESC',
            ]);
        }

        return $this->sendJson([
            'status' => 'ok',
            'data' => (array) Utils\ArrayHash::from($data),
        ]);
    }

    public function actionPost(array $params, Utils\ArrayHash $body): Application\IResponse
    {
        try {
            $newPersonUuid = $this->personService->createOne($body);
        } catch (Application\BadRequestException $ex) {
            return $this->sendJson([
                'status' => 'error',
                'message' => $ex->getMessage(),
            ], $ex->getCode());
        }

        return $this->sendJson([
            'status' => 'ok',
            'new-person-uuid' => $newPersonUuid,
        ]);
    }

}
