<?php

namespace Eaw\Traits;

use Eaw\Models\Report;

trait HasReports
{
    public function reports()
    {
        return $this->client->query($this->getFullPath() . '/reports')->setModel(Report::class);
    }
}
