<?php

namespace Eaw\Traits;

/**
 * @property array $headers Defined in Client.
 */
trait AuthenticatesClient
{
    /** @var string */
    protected $credentialsFile = EAW_DIR . '/.auth.json';

    /** @var array */
    protected $credentials;

    /**
     * @param string $url
     * @param array|null $parameters
     * @param array|null $data
     * @param array|null $files
     * @return array
     */
    abstract function create(string $url, array $parameters = null, array $data = null, array $files = null): array;

    /**
     * @return bool
     */
    protected function loadCredentials(): bool
    {
        $this->credentials = null;

        if (!file_exists($this->credentialsFile)) {
            return false;
        }

        $this->credentials = json_decode(file_get_contents($this->credentialsFile), true);

        return $this->credentials !== null;
    }

    /**
     * @return bool
     */
    protected function saveCredentials(): bool
    {
        if ($this->credentials === null) {
            return false;
        }

        return (bool) file_put_contents($this->credentialsFile, json_encode($this->credentials, JSON_PRETTY_PRINT));
    }

    /**
     * @return bool
     */
    public function isAuthenticated(): bool
    {
        if ($this->credentials === null) {
            $this->loadCredentials();
        }

        return $this->credentials !== null && $this->credentials['expires_at'] > time();
    }

    /**
     * @param array $data
     * @return bool
     */
    protected function auth(array $data): bool
    {
        logger()->debug('Authenticating...');

        $this->credentials = $this->create('/oauth/token', null, $data);

        $this->credentials['expires_at'] = time() + $this->credentials['expires_in'];

        $this->saveCredentials();

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

    /**
     * @param string $path
     * @param callable $callback
     * @return mixed
     */
    public function withCredentials(string $path, callable $callback)
    {
        $originalFile = $this->credentialsFile;

        $this->credentialsFile = $path;

        $this->loadCredentials();

        try {
            $return = $callback();
        } finally {
            $this->credentialsFile = $originalFile;

            $this->loadCredentials();
        }

        return $return;
    }
}
