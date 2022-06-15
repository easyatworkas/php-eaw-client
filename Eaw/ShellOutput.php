<?php

namespace Eaw;

use Psy\Output\ShellOutput as BaseShellOutput;

class ShellOutput extends BaseShellOutput
{
    protected $headerSkipped = false;

    public function write($messages, $newline = false, $type = 0)
    {
        if (!$this->headerSkipped && $messages === '' && $newline) {
            $this->headerSkipped = true;
            return;
        }

        parent::write($messages, $newline, $type);
    }
}
