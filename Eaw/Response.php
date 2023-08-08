<?php

namespace Eaw;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Psr\Http\Message\StreamInterface;

class Response
{
    protected $response;

    public function __construct(GuzzleResponse $response)
    {
        $this->response = $response;
    }

    /**
     * @return StreamInterface
     */
    public function getStream(): StreamInterface
    {
        return $this->response->getBody();
    }

    public function __toString()
    {
        return $this->getStream()->__toString();
    }
}
