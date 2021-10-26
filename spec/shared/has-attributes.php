<?php

describe('behavior from HasAttributes trait', function () {
    describe('->setAttributes()', function () {

    });

    describe('->getAttributes()', function () {
        it('should return all attributes', function () {
            $attributes = $this->instance->getAttributes();

            assert($attributes == $this->attributes);
        });
    });

    describe('->hasAttribute()', function () {

    });

    describe('->setAttribute()', function () {

    });

    describe('->getAttribute()', function () {

    });

    describe('->unsetAttribute()', function () {

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
