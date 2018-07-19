<?php

declare(strict_types = 1);

namespace App\Lib;

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
        parent::send($httpRequest, $httpResponse);
    }

}
