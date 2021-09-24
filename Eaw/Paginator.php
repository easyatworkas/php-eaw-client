<?php

namespace Eaw;

use Iterator;

class Paginator implements Iterator
{
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

    public function __construct(array $response, string $path = null, array $query = [])
    {
        $this->init($response, $path, $query);
    }

    protected function init(array $response, string $path, array $query)
    {
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

        // TODO: Go to page 1?
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

        $response = eaw()->read($this->path, $this->query);

        $this->init($response, $this->path, $this->query);
    }
}
