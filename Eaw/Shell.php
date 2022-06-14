<?php

namespace Eaw;

use Psy\Shell as Psy;

class Shell extends Psy
{
    protected function getHeader(): string
    {
        return '';
    }
}
