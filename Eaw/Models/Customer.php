<?php

namespace Eaw\Models;

use Eaw\Traits\HasProducts;
use Eaw\Traits\HasProperties;
use Eaw\Traits\HasReports;

class Customer extends Model
{
    use HasProperties;
    use HasProducts;
    use HasReports;

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
