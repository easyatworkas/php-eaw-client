<?php

namespace Eaw;

use Iterator;
use ReturnTypeWillChange;

/**
 * @template T
 * @property int $current_page
 * @property int $last_page
 * @property int $from
 * @property int $to
 * @property int $per_page
 * @property int $total
 */
class Paginator implements Iterator
{
    /** @var Client */
    protected $client;

    /** @var array Meta data from paginated response. */
    protected $meta;

    /** @var array<int|string, T> The paginated data. */
    protected $data;

    /** @var array<int, int|string> All keys. */
    protected $keys;

    /** @var int Index of current key in $this->keys. */
    protected $i;

    /** @var string */
    protected $path;

    /** @var array */
    protected $query;

    /** @var callable|null */
    protected $mapper;

    public function __construct(Client $client, string $path = null, array $query = [])
    {
        $this->client = $client;
        $this->path = $path;
        $this->query = $query;

        $this->rewind();
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

    /**
     * @return T
     */
    #[ReturnTypeWillChange]
    public function current()
    {
        $value = $this->data[$this->keys[$this->i]] ?? null;

        if ($this->mapper !== null) {
            $value = call_user_func($this->mapper, $value);
        }

        return $value;
    }

    #[ReturnTypeWillChange]
    public function next()
    {
        $this->i++;

        if (!$this->valid() && $this->hasNextPage()) {
            $this->loadPage($this->current_page + 1);
        }

        return $this;
    }

    #[ReturnTypeWillChange]
    public function key()
    {
        return $this->keys[$this->i] ?? null;
    }

    #[ReturnTypeWillChange]
    public function valid()
    {
        return array_key_exists($this->key(), $this->data);
    }

    #[ReturnTypeWillChange]
    public function rewind()
    {
        // loadPage() calls init(), which does reset i, but only if we're not already on page 1.
        $this->i = 0;

        $this->loadPage(1);

        return $this;
    }

    protected function hasNextPage()
    {
        return $this->current_page < $this->last_page;
    }

    public function loadPage(int $page, bool $force = false)
    {
        if ($page != $this->current_page || $force) {
            $this->query['page'] = $page;

            $this->init($this->path, $this->query);
        }

        return $this;
    }

    public function setMapper(callable $mapper)
    {
        $this->mapper = $mapper;

        return $this;
    }

    /**
     * @deprecated Don't use this method unless you really have to have everything from all pages.
     * @return T[]
     */
    public function all()
    {
        $allData = [
            $this->current_page => $this->data,
        ];

        for ($i = $this->current_page + 1; $i <= $this->last_page; $i++) {
            $this->client->readAsync($this->path, [ 'page' => $i ] + $this->query)
                ->then(function (array $response) use ($i, &$allData) {
                    $allData[$i] = $response['data'];
                });

            $this->client->tick();
        }

        $this->client->execute();

        ksort($allData);

        $allData = array_merge(... $allData);

        if ($this->mapper !== null) {
            foreach ($allData as $key => $value) {
                $allData[$key] = call_user_func($this->mapper, $value);
            }
        }

        return $allData;
    }

    public function each(callable $callback)
    {
        foreach ($this as $key => $value) {
            call_user_func($callback, $value, $key);
        }

        return $this;
    }

    public function __get($var)
    {
        return $this->meta[$var] ?? null;
    }
}
