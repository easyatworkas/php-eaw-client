<?php

namespace Eaw\Models;

use Eaw\Traits\HasProducts;
use Eaw\Traits\HasProperties;

class Customer extends Model
{
    use HasProperties;
    use HasProducts;

    protected $path = '/customers';

    public function settingGroup()
    {
        $instance = SettingGroup::newInstance([
            'id' => $this->getAttribute('setting_group_id'),
        ]);

        return SettingGroup::newQuery($instance->getFullPath());
    }

    public function employees()
    {
        return $this->client->query($this->getFullPath() . '/employees')->setModel(Employee::class);
    }
}
