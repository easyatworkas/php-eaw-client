<?php

describe('behavior from HasAttributes trait', function () {
    describe('->setAttributes()', function () {
        it('should take an array of attributes', function () {
            $this->instance->setAttributes($this->attributes);

            assert(true);
        });
    });

    describe('->getAttributes()', function () {
        it('should return all attributes', function () {
            $attributes = $this->instance->getAttributes();

            assert($attributes == $this->attributes);
        });
    });

    describe('->hasAttribute()', function () {
        it('returns true for known attributes', function () {
            $this->instance->setAttributes([ 'foo' => 'bar' ]);

            assert($this->instance->hasAttribute('foo'));
        });

        it('returns false for unknown attributes', function () {
            $this->instance->setAttributes([]);

            assert(!$this->instance->hasAttribute('foo'));
        });
    });

    describe('->setAttribute()', function () {
        it('should take an attribute and a value', function () {
            $this->instance->setAttributes([]);

            foreach ($this->attributes as $attribute => $value) {
                $this->instance->setAttribute($attribute, $value);
            }

            assert(true);
        });
    });

    describe('->getAttribute()', function () {
        it('should return value of known attributes', function () {
            foreach ($this->attributes as $attribute => $value) {
                assert($this->instance->getAttribute($attribute) === $value);
            }
        });

        it('should return null for unknown attributes', function () {
            $this->instance->setAttributes([]);

            assert($this->instance->getAttribute('foo') === null);
        });
    });

    describe('->unsetAttribute()', function () {
        it('should unset known attributes', function () {
            foreach ($this->attributes as $attribute => $value) {
                assert($this->instance->getAttribute($attribute) === $value);

                $this->instance->unsetAttribute($attribute);

                assert($this->instance->getAttribute($attribute) === null);
            }
        });

        it('should accept unknown attributes', function () {
            $this->instance->setAttributes([]);

            $this->instance->unsetAttribute('foo');

            assert(true);
        });
    });

    describe('->syncOriginal()', function () {

    });

    describe('->getOriginal()', function () {

    });

    describe('->getDirty()', function () {

    });

    describe('->isDirty()', function () {

    });

    describe('->toArray()', function () {

    });

    describe('->toJson()', function () {

    });

    describe('magic', function () {
        describe('->__isset()', function () {

        });

        describe('->__get()', function () {
            it('should return value of given attribute', function () {
                foreach ($this->attributes as $attribute => $expected) {
                    $value = $this->instance->$attribute;

                    assert($value === $expected);
                }
            });
        });

        describe('->__set()', function () {

        });

        describe('->__unset()', function () {

        });

        describe('->__toString()', function () {

        });
    });

    describe('ArrayAccess', function () {
        describe('->offsetExists()', function () {

        });

        describe('->offsetGet()', function () {

        });

        describe('->offsetSet()', function () {

        });

        describe('->offsetUnset()', function () {

        });
    });

    describe('->jsonSerialize()', function () {

    });
});
