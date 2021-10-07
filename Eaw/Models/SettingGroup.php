<?php

namespace Eaw\Models;

use Eaw\Traits\HasProperties;

class SettingGroup extends Model
{
    use HasProperties;

    protected $path = '/setting_groups';

    public function customers()
    {
        return $this->client->query($this->getFullPath() . '/members')->setModel(Customer::class);
    }
}
