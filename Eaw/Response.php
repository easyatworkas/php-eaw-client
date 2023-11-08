<?php

namespace Eaw;

use Exception;
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
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    /**
     * @return string
     */
    public function getReasonPhrase(): string
    {
        return $this->response->getReasonPhrase();
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

    /**
     * @return array
     * @throws Exception
     */
    public function decodeJson()
    {
        $encoded = (string) $this->getStream();

        if ($encoded === '') {
            $encoded = json_encode($encoded);
        }

        $decoded = json_decode($encoded, true);

        if ($decoded === null) {
            throw new Exception(json_last_error_msg());
        }

        if ($decoded === '') {
            $decoded = [];
        }

        return $decoded;
    }

    public function __toString()
    {
        return $this->getStream()->__toString();
    }
}
