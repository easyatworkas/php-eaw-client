<?php

namespace Eaw;

use Eaw\Traits\Singleton;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class Logger extends AbstractLogger
{
    use Singleton;

    const ESCAPE = "\033[%sm";

    const DARK = 30;
    const LIGHT = 90;

    const BLACK = 0;
    const RED = 1;
    const GREEN = 2;
    const YELLOW = 3;
    const BLUE = 4;
    const MAGENTA = 5;
    const CYAN = 6;
    const GRAY = 7;

    const DEFAULT = 39;

    /** @var callable */
    protected $formatter;

    public function setFormatter(callable $formatter)
    {
        $this->formatter = $formatter;
    }

    public function color(string $string, int $color)
    {
        return sprintf(static::ESCAPE, $color) . $string . sprintf(static::ESCAPE, static::DEFAULT);
    }

    public function log($level, $message, array $context = [])
    {
        switch ($level) {
            case LogLevel::INFO:
                $message = $this->color($message, static::DARK + static::GREEN);
                break;

            case LogLevel::ERROR:
                $message = $this->color($message, static::DARK + static::RED);
                break;
        }

        if ($context['eol'] ?? true) {
            $message .= PHP_EOL;
        }

        if ($formatter = $context['formatter'] ?? $this->formatter) {
            $message = call_user_func($formatter, $this, $level, $message, $context);
        }

        fwrite(STDERR, $message);
    }
}
