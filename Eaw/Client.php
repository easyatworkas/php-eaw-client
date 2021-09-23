<?php

namespace Eaw;

use Eaw\Traits\Singleton;
use GuzzleHttp\Client as Guzzle;
use Psr\Http\Message\ResponseInterface;

class Client
{
    use Singleton;

    /**
     * @var Guzzle https://docs.guzzlephp.org/en/6.5/
     */
    protected $guzzle;

    /**
     * @var string Base API URL.
     */
    protected $baseUrl = 'https://api.easyatwork.com';

    /**
     * @var string[] Headers to include in all requests.
     */
    protected $headers = [
        'Content-Type' => 'application/x-www-form-urlencoded',
    ];

    protected function __construct()
    {
        $this->guzzle = new Guzzle();
    }

    /**
     * @param string $method
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @return ResponseInterface
     */
    protected function request(string $method = 'GET', string $path = '/', array $parameters = null, array $data = null)
    {
        $url = $this->baseUrl . $path;

        if ($parameters) {
            $url .= '?' . http_build_query($parameters);
        }

        return $this->guzzle->request($method, $url, array_filter([
            'headers' => $this->headers,
            'body' => $data === null ? null : http_build_query($data),
        ]));
    }

    /**
     * Crud.
     *
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @return ResponseInterface
     */
    public function create(string $path, array $parameters = null, array $data = null)
    {
        return $this->request('POST', $path, $parameters, $data);
    }

    /**
     * cRud.
     *
     * @param string $path
     * @param array|null $parameters
     * @return ResponseInterface
     */
    public function read(string $path, array $parameters = null)
    {
        return $this->request('GET', $path, $parameters);
    }

    /**
     * crUd.
     *
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @return ResponseInterface
     */
    public function update(string $path, array $parameters = null, array $data = null)
    {
        return $this->request('PUT', $path, $parameters, $data);
    }

    /**
     * cruD.
     *
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @return ResponseInterface
     */
    public function delete(string $path, array $parameters = null, array $data = null)
    {
        return $this->request('DELETE', $path, $parameters, $data);
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function auth(array $data)
    {
        $response = $this->create('/oauth/token', null, $data);

        if ($response->getStatusCode() != Http::OK) {
            return false;
        }

        $data = json_decode($response->getBody(), true);

        // TODO: Store token and expires_in somewhere more permanent.
        $this->headers['Authorization'] = $data['token_type'] . ' ' . $data['access_token'];

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
}
