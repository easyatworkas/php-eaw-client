<?php

namespace Eaw\Exceptions;

use Eaw\Response;
use Exception;

class ResponseException extends Exception
{
    /** @var Response */
    protected $response;

    public function __construct(Response $response, Exception $previous)
    {
        parent::__construct(
            $response->getStatusCode() . ' ' . $response->getReasonPhrase(),
            $response->getStatusCode(),
            $previous
        );

        $this->response = $response;
    }

    /**
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}
