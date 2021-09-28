<?php

namespace Eaw;

use Eaw\Traits\Singleton;
use GuzzleHttp\Client as Guzzle;

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
    protected $headers = [];

    protected function __construct()
    {
        $this->guzzle = new Guzzle();
    }

    /**
     * @param string $method
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @return array
     */
    protected function request(string $method = 'GET', string $path = '/', array $parameters = null, array $data = null, array $files = null)
    {
        $url = $this->baseUrl . $path;

        if ($parameters) {
            $url .= '?' . http_build_query($parameters);
        }

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
                        'value' => urldecode($val),
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

        /*print_r([
            'method' => $method,
            'path' => $path,
            'parameters' => $parameters,
            'data' => $data,
        ]);*/

        $response = $this->guzzle->request($method, $url, array_filter($options));

        return json_decode($response->getBody(), true);
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

    public function query(string $path)
    {
        return new QueryBuilder($this, $path);
    }
}
