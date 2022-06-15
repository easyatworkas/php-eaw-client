<?php

namespace Eaw;

use Eaw\Traits\IsSingleton;
use Psr\Log\AbstractLogger;
use Psr\Log\LogLevel;

class Logger extends AbstractLogger
{
    use IsSingleton;

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

    protected $indentLevel = 0;

    public function setFormatter(callable $formatter)
    {
        $this->formatter = $formatter;
    }

    public function group()
    {
        $this->indentLevel++;
    }

    public function ungroup()
    {
        $this->indentLevel--;
    }

    public function color(string $string, int $color)
    {
        return sprintf(static::ESCAPE, $color) . $string . sprintf(static::ESCAPE, static::DEFAULT);
    }

    protected function format(self $self, string $level, string $message, array $context)
    {
        if ($context['indent'] ?? false) {
            $message = str_repeat('  ', $context['indent']) . $message;
        }

        if ($context['color']) {
            $message = $this->color($message, $context['color']);
        }

        if ($context['timestamp'] ?? true) {
            $message = $this->color('[' . date('Y-m-d H:i:s') . ']', static::DARK + static::YELLOW) . ' ' . $message;
        }

        if ($context['eol'] ?? true) {
            $message .= PHP_EOL;
        }

        return $message;
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array{indent?: int, color?: int, timestamp?: bool, eol?: bool} $context
     */
    public function log($level, $message, array $context = [])
    {
        $context['color'] = $context['color'] ?? (function ($level) {
            switch ($level) {
                case LogLevel::INFO: return static::DARK + static::GREEN;
                case LogLevel::NOTICE: return static::DARK + static::YELLOW;
                case LogLevel::ERROR: return static::DARK + static::RED;
            }
        })($level);

        $context['indent'] = $context['indent'] ?? $this->indentLevel;

        $context['formatter'] = $context['formatter'] ?? $this->formatter ?? [ $this, 'format' ];

        $message = call_user_func_array($context['formatter'], [ $this, $level, $message, $context ]);

        fwrite(STDERR, $message);
    }
}
