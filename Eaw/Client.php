<?php

namespace Eaw;

use Eaw\Exceptions\ResponseException;
use Eaw\Traits\AuthenticatesClient;
use Eaw\Traits\BuildsHttpRequestData;
use Eaw\Traits\DownloadsFiles;
use Eaw\Traits\MakesCrudRequests;
use Eaw\Traits\IsSingleton;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Psr\Log\LoggerInterface;

class Client
{
    use IsSingleton;
    use BuildsHttpRequestData;
    use MakesCrudRequests;
    use AuthenticatesClient;
    use DownloadsFiles;

    /**
     * @var Guzzle https://docs.guzzlephp.org/en/6.5/
     */
    protected $guzzle;

    /**
     * @var CurlMultiHandler
     */
    protected $handler;

    /**
     * @var string Base API URL.
     */
    protected $baseUrl = 'https://api.easyatwork.com';

    /**
     * @var string[] Headers to include in all requests.
     */
    protected $headers = [
        'User-Agent' => 'php-eaw-client',
    ];

    /**
     * @var bool[] Client options.
     */
    protected $options = [
        'catch_rate_limit' => true,
        'follow_url_hint' => true,
    ];

    /** @var LoggerInterface */
    protected $logger;

    protected function __construct()
    {
        $this->handler = new CurlMultiHandler([
            'select_timeout' => 0.1,
        ]);

        $this->guzzle = new Guzzle([
            'handler' => HandlerStack::create($this->handler),
        ]);

        $this->logger = Logger::getInstance()->getLogger('client');
    }

    /**
     * @param string $url
     * @param bool $force Bypass sanity checking.
     * @return bool
     */
    public function setBaseUrl(string $url, bool $force = false): bool
    {
        if (!preg_match('/^https:\/\/.+\.(?:eatw.io|easyatwork.com)\/?$/i', $url)) {
            if ($force) {
                $this->logger()->warning('Bypassing sanity check for API URL! Do NOT do this unless you are ABSOLUTELY SURE you know what you are doing!');
            } else {
                return false;
            }
        }

        $this->baseUrl = $url;

        return true;
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    /**
     * @param array $options
     * @return array
     */
    public function setOptions(array $options): array
    {
        return $this->options = $options + $this->options;
    }

    /**
     * @return LoggerInterface
     */
    protected function logger(): LoggerInterface
    {
        return $this->logger;
    }

    /**
     * Tick the cURL event loop.
     */
    public function tick()
    {
        $this->handler->tick();
    }

    /**
     * Tick until all requests have completed.
     */
    public function execute()
    {
        $this->handler->execute();
    }

    /**
     * @param Response $response
     * @return false|int
     */
    protected function isRateLimited(Response $response)
    {
        if (!$this->options['catch_rate_limit']) {
            return false;
        }

        if ($response->getStatusCode() != 429) {
            return false;
        }

        return $response->getHeader('Retry-After')[0] ?? 10;
    }

    /**
     * @param Response $response
     * @return bool
     */
    protected function followUrlHint(Response $response)
    {
        if (!$this->options['follow_url_hint']) {
            return false;
        }

        if (!$response->hasHeader('X-API-URL')) {
            return false;
        }

        $apiUrl = $response->getHeader('X-API-URL')[0];

        if ($this->getBaseUrl() == $apiUrl) {
            return false;
        }

        return $this->setBaseUrl($apiUrl);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @param array|null $files
     * @param array $options
     * @return Response|array The decoded JSON, or a Response if $options['raw'] is truthy.
     */
    public function request(string $method = 'GET', string $path = '/', array $parameters = null, array $data = null, array $files = null, array $options = [])
    {
        $options['synchronous'] = true;

        return $this->requestAsync($method, $path, $parameters, $data, $files, $options)->wait(true);
    }

    /**
     * @param string $method
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @param array|null $files
     * @param array $options
     * @return PromiseInterface<Response|array> Promise that resolves to the decoded JSON, or a Response if $options['raw'] is truthy.
     */
    public function requestAsync(string $method = 'GET', string $path = '/', array $parameters = null, array $data = null, array $files = null, array $options = []): PromiseInterface
    {
        return $this->guzzle->requestAsync(
                $method,
                $this->buildRequestUrl($path, $parameters),
                $this->buildRequestOptions($data, $files) + $options
            )
            ->then(function (GuzzleResponse $guzzleResponse) use ($options) {
                $response = new Response($guzzleResponse);

                if ($this->followUrlHint($response)) {
                    $this->logger()->debug('Switching API URL to "' . $this->getBaseUrl() . '"...');
                }

                if ($options['raw'] ?? false) {
                    return $response;
                }

                return $response->decodeJson();
            })
            ->otherwise(function ($exception) use ($method, $path, $parameters, $data, $files) {
                if ($exception instanceof RequestException && $exception->hasResponse()) {
                    $response = new Response($exception->getResponse());

                    if (false !== $retryAfter = $this->isRateLimited($response)) {
                        if ($retryAfter) {
                            $this->logger()->notice('Rate limit reached. Retrying in ' . $retryAfter . ' seconds...');
                        }

                        return $this->requestAsync($method, $path, $parameters, $data, $files, [ 'delay' => $retryAfter * 1000 ]);
                    }

                    throw new ResponseException($response, $exception);
                }

                throw $exception;
            });
    }

    /**
     * @param string $path
     * @param array $parameters
     * @return Paginator
     */
    public function readPaginated(string $path, array $parameters = []): Paginator
    {
        return new Paginator(
            $this,
            $path,
            $parameters
        );
    }

    /**
     * @param string $path
     * @return QueryBuilder
     */
    public function query(string $path): QueryBuilder
    {
        return new QueryBuilder($this, $path);
    }

    /**
     * @param string $path
     * @return QueryBuilderImmutable
     */
    public function queryImmutable(string $path)
    {
        return new QueryBuilderImmutable($this->query($path));
    }

    /**
     * @return string|null
     */
    public function apiVersion()
    {
        return $this->request('get', '/status', null, null, null, [ 'raw' => true ])->getHeader('X-Version')[0] ?? null;
    }
}
