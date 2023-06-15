<?php

namespace Eaw;

class QueryBuilderImmutable
{
    protected $queryBuilder;

    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param string $method
     * @param array $arguments
     * @return self
     */
    public function __call(string $method, array $arguments)
    {
        $clone = clone $this->queryBuilder;

        $return = call_user_func_array([ $clone, $method ], $arguments);

        return $return === $clone ? new self($clone) : $return;
    }
}
