<?php

namespace Eaw;

use Iterator;

class Paginator implements Iterator
{
    /** @var Client */
    protected $client;

    /** @var array Meta data from paginated response. */
    protected $meta;

    /** @var array The paginated data. */
    protected $data;

    /** @var int[]|string[] All keys. */
    protected $keys;

    /** @var int Index of current key in $this->keys. */
    protected $i;

    protected $path;

    protected $query;

    public function __construct(Client $client, string $path = null, array $query = [])
    {
        $this->client = $client;
        $this->path = $path;
        $this->query = $query;
    }

    protected function init(string $path, array $query)
    {
        $response = $this->client->read($path, $query);

        $this->data = $response['data'] ?? [];
        unset($response['data']);
        $this->meta = $response;

        $this->keys = array_keys($this->data);
        $this->i = 0;

        $this->path = $path;
        $this->query = $query;
    }

    public function current()
    {
        return $this->data[$this->i] ?? null;
    }

    public function next()
    {
        $this->i++;

        if (!$this->valid() && $this->hasNextPage()) {
            $this->loadPage($this->currentPage() + 1);
        }
    }

    public function key()
    {
        return $this->keys[$this->i] ?? null;
    }

    public function valid()
    {
        return array_key_exists($this->key(), $this->data);
    }

    public function rewind()
    {
        $this->i = 0;

        $this->loadPage(1);
    }

    protected function currentPage()
    {
        return $this->meta['current_page'] ?? null;
    }

    protected function lastPage()
    {
        return $this->meta['last_page'] ?? null;
    }

    protected function hasNextPage()
    {
        return $this->currentPage() < $this->lastPage();
    }

    public function loadPage(int $page)
    {
        $this->query['page'] = $page;

        $this->init($this->path, $this->query);
    }
}
