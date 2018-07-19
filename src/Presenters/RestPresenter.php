<?php

declare(strict_types = 1);

namespace App\Presenters;

use App\Lib;
use Nette\Application;
use Nette\Http;
use Nette\Utils;

abstract class RestPresenter implements Application\IPresenter
{

    public const CORS_HEADERS = [
        'Allow' => 'OPTIONS, GET, POST',
        'Access-Control-Allow-Origin' => '*',
        'Access-Control-Allow-Headers' => 'Content-Type',
    ];

    /** @var Http\Request */
    private $request;

    public function __construct(Http\Request $request)
    {
        $this->request = $request;
    }

    public function run(Application\Request $appRequest): Application\IResponse
    {
        if ($this->isPreflight($this->request)) {
            return new Application\Responses\CallbackResponse(function (Http\IRequest $httpRequest, Http\IResponse $httpResponse): void {
                foreach (self::CORS_HEADERS as $header => $value) {
                    $httpResponse->setHeader($header, $value);
                }
            });
        }

        if ($this->isRest($this->request)) {
            $method = $this->request->getMethod();
            $params = $appRequest->getParameters();
            switch ($method) {
                case 'GET':
                    return $this->actionGet($params, $this->request);

                case 'POST':
                    $body = $this->decodeRequestBody();
                    return $this->actionPost($params, $body, $this->request);

                default:
                    return new Lib\JsonResponse(Http\IResponse::S405_METHOD_NOT_ALLOWED, [
                        'status' => 'unknow HTTP method',
                    ]);
            }
        }

        throw new Application\BadRequestException(
            'This presenter can only handle "application/json" content-type',
            Http\IResponse::S400_BAD_REQUEST
        );
    }

    protected function sendJson(array $data, int $statusCode = Http\IResponse::S200_OK): Application\IResponse
    {
        return new Lib\JsonResponse($statusCode, $data);
    }

    private function isPreflight(Http\Request $request): bool
    {
        $method = $request->getMethod();

        return ($method === 'OPTIONS');
    }

    private function isRest(Http\Request $request): bool
    {
        $contentType = $request->getHeader('Content-Type');

        return ($contentType === 'application/json');
    }

    private function decodeRequestBody(): Utils\ArrayHash
    {
        $requestBody = \file_get_contents('php://input');
        $requestBodyJson = Utils\Json::decode($requestBody);
        $body = Utils\ArrayHash::from($requestBodyJson);

        return $body;
    }

}
