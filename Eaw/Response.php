<?php

namespace Eaw;

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Psr\Http\Message\StreamInterface;

class Response
{
    protected GuzzleResponse $response;

    public function __construct(GuzzleResponse $response)
    {
        $this->response = $response;
    }

    /**
     * @param string $header
     * @return bool
     */
    public function hasHeader(string $header): bool
    {
        return $this->response->hasHeader($header);
    }

    /**
     * @param string $header
     * @return string[]
     */
    public function getHeader(string $header): array
    {
        return $this->response->getHeader($header);
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->response->getHeaders();
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
