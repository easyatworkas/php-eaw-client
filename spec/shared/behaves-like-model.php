<?php

use Eaw\QueryBuilder;

describe('behavior inherited from Model', function () {
    if (!isset($this->model)) {
        throw new Exception('Model not defined');
    }

    if (!isset($this->attributes)) {
        $this->attributes = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];
    }

    describe('static methods', function () {
        describe('::newInstance()', function () {
            it('should return a new instance', function () {
                $instance = $this->model::newInstance();

                assert($instance instanceof $this->model);
            });
        });

        describe('::newQuery()', function () {
            it('should return a new query', function () {
                $query = $this->model::newQuery();

                assert($query instanceof QueryBuilder);
            });
        });

        describe('::__callStatic()', function () {
            it('should return a new query', function () {
                $query = $this->model::someParameter(true);

                assert($query instanceof QueryBuilder);
            });
        });
    });

    beforeEach(function () {
        $this->instance = $this->model::newInstance($this->attributes);
    });

    require('has-attributes.php');

    describe('->setPath()', function () {
        it('should return $this', function () {
            $self = $this->instance->setPath('/foo/bar');

            assert($self instanceof $this->model);
        });
    });

    describe('->getPath()', function () {
        it('should return the defined path', function () {
            $this->instance->setPath('/foo/bar');

            $path = $this->instance->getPath();

            assert($path == '/foo/bar');
        });
    });

    describe('->getFullPath()', function () {
        it('should return the full path', function () {
            $this->instance->setPath('/foo/bar');
            $this->instance->setKey(1337);

            $fullPath = $this->instance->getFullPath();

            assert($fullPath == '/foo/bar/1337');
        });
    });
});
