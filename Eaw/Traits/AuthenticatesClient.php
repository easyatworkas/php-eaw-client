<?php

namespace Eaw\Traits;

/**
 * @property array $headers Defined in Client.
 */
trait AuthenticatesClient
{
    /**
     * @param string $url
     * @param array|null $parameters
     * @param array|null $data
     * @param array|null $files
     * @return array
     */
    abstract function create(string $url, array $parameters = null, array $data = null, array $files = null): array;

    /**
     * @param array $data
     * @return bool
     */
    protected function auth(array $data): bool
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
    public function clientAuth(int $clientId, string $clientSecret): bool
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
    public function userAuth(string $username, string $password): bool
    {
        return $this->auth([
            'grant_type' => 'password',
            'client_id' => '2', // TODO: Magic number.
            'username' => $username,
            'password' => $password,
        ]);
    }
}
