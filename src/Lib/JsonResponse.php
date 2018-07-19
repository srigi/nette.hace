<?php

declare(strict_types = 1);

namespace App\Lib;

use App\Presenters\RestPresenter;
use Nette\Application\Responses;
use Nette\Http;

class JsonResponse extends Responses\JsonResponse
{

    /** @var int */
    private $code;

    public function __construct(int $code, array $payload)
    {
        parent::__construct($payload, null);

        $this->code = $code;
    }

    public function send(Http\IRequest $httpRequest, Http\IResponse $httpResponse): void
    {
        $httpResponse->setCode($this->code);

        foreach (RestPresenter::CORS_HEADERS as $header => $value) {
            $httpResponse->setHeader($header, $value);
        }

        parent::send($httpRequest, $httpResponse);
    }

}
