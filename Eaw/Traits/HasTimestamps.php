<?php

namespace Eaw\Traits;

trait HasTimestamps
{
    protected $createdAtColumn = 'created_at';

    protected $updatedAtColumn = 'updated_at';
}
