<?php

namespace Eaw\Traits;

use Eaw\Response;
use GuzzleHttp\Promise\PromiseInterface;

trait MakesCrudRequests
{
    /**
     * @param string $method
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @param array|null $files
     * @param array $options
     * @return Response|array
     */
    abstract function request(string $method, string $path, array $parameters = null, array $data = null, array $files = null, array $options = []);

    /**
     * @param string $method
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @param array|null $files
     * @param array $options
     * @return PromiseInterface<Response|array>
     */
    abstract function requestAsync(string $method, string $path, array $parameters = null, array $data = null, array $files = null, array $options = []): PromiseInterface;

    /**
     * Crud.
     *
     * @param string $path
     * @param array|null $parameters
     * @param array|null $data
     * @param array $files
     * @return array
     */
    public function create(string $path, array $parameters = null, array $data = null, array $files = null): array
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
    public function createAsync(string $path, array $parameters = null, array $data = null, array $files = null): PromiseInterface
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
    public function read(string $path, array $parameters = null): array
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
    public function readAsync(string $path, array $parameters = null): PromiseInterface
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
    public function update(string $path, array $parameters = null, array $data = null): array
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
    public function updateAsync(string $path, array $parameters = null, array $data = null): PromiseInterface
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
    public function delete(string $path, array $parameters = null, array $data = null): array
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
    public function deleteAsync(string $path, array $parameters = null, array $data = null): PromiseInterface
    {
        return $this->requestAsync('DELETE', $path, $parameters, $data);
    }
}
