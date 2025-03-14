<?php

namespace Eaw;

/**
 * @template T
 */
class QueryBuilder
{
    /** @var Client */
    protected $client;

    /** @var string */
    protected $path;

    /** @var array */
    protected $pathVariables = [];

    /** @var array */
    protected $query = [];

    /** @var class-string<T> */
    protected $model;

    public function __construct(Client $client, string $path)
    {
        $this->client = $client;
        $this->path = $path;
    }

    /**
     * @template C
     * @param class-string<C> $model
     * @return self<C>
     */
    public function setModel(string $model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return string[]
     */
    protected function getPlaceholders()
    {
        preg_match_all('/{.+?}/', $this->path, $placeholders);

        return $placeholders[0];
    }

    /**
     * @return string
     */
    protected function getPath()
    {
        $path = $this->path;

        foreach ($this->getPlaceholders() as $placeholder) {
            $path = str_replace($placeholder, $this->pathVariables[$placeholder] ?? $placeholder, $path);
        }

        return $path;
    }

    /**
     * @return array|T
     */
    public function get($id = null)
    {
        $response = $this->client->read($this->getPath() . ($id === null ? '' : '/' . $id), $this->query);

        if ($this->model !== null) {
            return $this->model::newInstance($response)->setPath($this->getPath());
        }

        return $response;
    }

    /**
     * @return Paginator<array>|Paginator<T>
     */
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

    /**
     * @param string $method
     * @param array $arguments
     * @return $this
     */
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
