<?php

namespace Eaw;

use Eaw\Traits\Singleton;
use GuzzleHttp\Client as Guzzle;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Handler\CurlMultiHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\ResponseInterface;

class Client
{
    use Singleton;

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
    protected $headers = [];

    /**
     * @var bool[] Client options.
     */
    protected $options = [
        'catch_rate_limit' => true,
    ];

    protected function __construct()
    {
        $this->handler = new CurlMultiHandler([
            'select_timeout' => 0.1,
        ]);

        $this->guzzle = new Guzzle([
            'handler' => HandlerStack::create($this->handler),
        ]);
    }

    /**
     * @param array $options
     * @return array
     */
    public function setOptions(array $options)
    {
        return $this->options = $options + $this->options;
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
     * @param string $path
     * @param array|null $parameters
     * @return string
     */
    protected function buildRequestUrl(string $path = '/', array $parameters = null)
    {
        $url = $this->baseUrl . $path;

        if ($parameters) {
            $url .= '?' . http_build_query($parameters);
        }

        return $url;
    }

    /**
     * @param array|null $data
     * @param array|null $files
     * @return array
     */
    protected function buildRequestOptions(array $data = null, array $files = null)
    {
        $options = [
            'headers' => $this->headers,
            'multipart' => [],
        ];

        if ($files) {
            if ($data) {
                // Super hacky way to build a multipart request.
                foreach (explode('&', http_build_query($data)) as $pair) {
                    list($var, $val) = explode('=', $pair, 2);

                    $options['multipart'][] = [
                        'name' => urldecode($var),
                        'contents' => urldecode($val),
                    ];
                }
            }

            foreach ($files as $name => $file) {
                if (is_resource($file)) {
                    $file = [
                        'handle' => $file,
                        'filename' => stream_get_meta_data($file)['uri'] ?? null,
                    ];
                }

                $options['multipart'][] = array_filter([
                    'name' => $name,
                    'contents' => $file['handle'] ?? null,
                    'filename' => $file['filename'] ?? null,
                ]);
            }
        } else {
            $options['json'] = $data;
        }

        return array_filter($options);
    }

    /**
     * @param ResponseInterface $response
     * @return false|int
     */
    protected function isRateLimited(ResponseInterface $response)
    {
        if ($this->options['catch_rate_limit'] && $response->getStatusCode() == 429) {
            return $response->getHeader('Retry-After')[0] ?? 10;
        }

        return false;
    }

    /**
     * @param string $method
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @param array|null $files
     * @param array $options
     * @return array
     */
    protected function request(string $method = 'GET', string $path = '/', array $parameters = null, array $data = null, array $files = null, array $options = [])
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
     * @return PromiseInterface<array>
     */
    public function requestAsync(string $method = 'GET', string $path = '/', array $parameters = null, array $data = null, array $files = null, array $options = [])
    {
        return $this->guzzle->requestAsync(
                $method,
                $this->buildRequestUrl($path, $parameters),
                $this->buildRequestOptions($data, $files) + $options
            )
            ->then(function (ResponseInterface $response) {
                return json_decode($response->getBody(), true);
            })
            ->otherwise(function (ClientException $exception) use ($method, $path, $parameters, $data, $files) {
                if (false !== $retryAfter = $this->isRateLimited($exception->getResponse())) {
                    if ($retryAfter) {
                        logger()->notice('Rate limit reached. Retrying in ' . $retryAfter . ' seconds...');
                    }

                    return $this->requestAsync($method, $path, $parameters, $data, $files, [ 'delay' => $retryAfter * 1000 ]);
                }

                throw $exception;
            });
    }

    /**
     * Crud.
     *
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @param array $files
     * @return array
     */
    public function create(string $path, array $parameters = null, array $data = null, array $files = null)
    {
        return $this->request('POST', $path, $parameters, $data, $files);
    }

    /**
     * Crud async.
     *
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @param array $files
     * @return PromiseInterface<array>
     */
    public function createAsync(string $path, array $parameters = null, array $data = null, array $files = null)
    {
        return $this->requestAsync('POST', $path, $parameters, $data, $files);
    }

    /**
     * cRud.
     *
     * @param string $path
     * @param array|null $parameters
     * @return array
     */
    public function read(string $path, array $parameters = null)
    {
        return $this->request('GET', $path, $parameters);
    }

    /**
     * cRud async.
     *
     * @param string $path
     * @param array|null $parameters
     * @return PromiseInterface<array>
     */
    public function readAsync(string $path, array $parameters = null)
    {
        return $this->requestAsync('GET', $path, $parameters);
    }

    /**
     * crUd.
     *
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @return array
     */
    public function update(string $path, array $parameters = null, array $data = null)
    {
        return $this->request('PUT', $path, $parameters, $data);
    }

    /**
     * crUd async.
     *
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @return PromiseInterface<array>
     */
    public function updateAsync(string $path, array $parameters = null, array $data = null)
    {
        return $this->requestAsync('PUT', $path, $parameters, $data);
    }

    /**
     * cruD.
     *
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @return array
     */
    public function delete(string $path, array $parameters = null, array $data = null)
    {
        return $this->request('DELETE', $path, $parameters, $data);
    }

    /**
     * cruD async.
     *
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @return PromiseInterface<array>
     */
    public function deleteAsync(string $path, array $parameters = null, array $data = null)
    {
        return $this->requestAsync('DELETE', $path, $parameters, $data);
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function auth(array $data)
    {
        $response = $this->create('/oauth/token', null, $data);

        // TODO: Store token and expires_in somewhere more permanent.
        $this->headers['Authorization'] = $response['token_type'] . ' ' . $response['access_token'];

        return true;
    }

    /**
     * @param int $clientId
     * @param string $clientSecret
     * @return bool
     */
    public function clientAuth(int $clientId, string $clientSecret)
    {
        return $this->auth([
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);
    }

    /**
     * @param string $username
     * @param string $password
     * @return bool
     */
    public function userAuth(string $username, string $password)
    {
        return $this->auth([
            'grant_type' => 'password',
            'client_id' => '2', // TODO: Magic number.
            'username' => $username,
            'password' => $password,
        ]);
    }

    /**
     * @param string $path
     * @param array $parameters
     * @return Paginator
     */
    public function readPaginated(string $path, array $parameters = [])
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
    public function query(string $path)
    {
        return new QueryBuilder($this, $path);
    }
}
