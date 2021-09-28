<?php

namespace Eaw;

class QueryBuilder
{
    protected $client;

    protected $path;

    protected $pathVariables = [];

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

    protected function getPlaceholders()
    {
        preg_match_all('/{.+?}/', $this->path, $placeholders);

        return $placeholders[0];
    }

    protected function getPath()
    {
        $path = $this->path;

        foreach ($this->getPlaceholders() as $placeholder) {
            $path = str_replace($placeholder, $this->pathVariables[$placeholder] ?? $placeholder, $path);
        }

        return $path;
    }

    public function get($id = null)
    {
        $response = $this->client->read($this->getPath() . ($id === null ? '' : '/' . $id), $this->query);

        if ($this->model !== null) {
            return $this->model::newInstance($response)->setPath($this->getPath());
        }

        return $response;
    }

    public function getAll()
    {
        $iterator = $this->client->readPaginated($this->getPath(), $this->query);

        if ($this->model !== null) {
            $iterator->setMapper(function (array $attributes) {
                return $this->model::newInstance($attributes)->setPath($this->getPath());
            });
        }

        return $iterator;
    }

    public function __call(string $method, array $arguments)
    {
        $parameter = strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $method));;

        if (in_array($placeholder = '{' . $parameter . '}', $this->getPlaceholders())) {
            $this->pathVariables[$placeholder] = $arguments[0] ?? null;
        } else {
            $this->query[$parameter] = count($arguments) > 1 ? $arguments : $arguments[0];
        }

        return $this;
    }
}
