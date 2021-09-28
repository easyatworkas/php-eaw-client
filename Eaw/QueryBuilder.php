<?php

namespace Eaw;

class QueryBuilder
{
    protected $client;

    protected $path;

    protected $query = [];

    protected $model;

    public function __construct(Client $client, string $path)
    {
        $this->client = $client;
        $this->path = $path;
    }

    public function setModel(string $model)
    {
        $this->model = $model;

        return $this;
    }

    public function get($id = null)
    {
        $response = $this->client->read($this->path . ($id === null ? '' : '/' . $id), $this->query);

        if ($this->model !== null) {
            return $this->model::newInstance($response)->setPath($this->path);
        }

        return $response;
    }

    public function getAll()
    {
        $iterator = $this->client->readPaginated($this->path, $this->query);

        if ($this->model !== null) {
            $iterator->setMapper(function (array $attributes) {
                return $this->model::newInstance($attributes)->setPath($this->path);
            });
        }

        return $iterator;
    }

    public function __call(string $method, array $arguments)
    {
        $parameter = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $method));;

        $this->query[$parameter] = count($arguments) > 1 ? $arguments : $arguments[0];

        return $this;
    }
}
