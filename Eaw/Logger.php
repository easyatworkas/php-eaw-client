<?php

namespace Eaw;

use Eaw\Traits\IsSingleton;
use Exception;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger as Monolog;
use Psr\Log\LoggerInterface;

class Logger implements FormatterInterface
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

    const RESET = 39;

    /** @var string */
    protected $defaultName = 'default';

    protected $defaultFormat = '{dyellow}[{datetime}]{reset} {lblack}{channel}.{reset}{level}: {message}{eol}';

    /** @var Monolog[] */
    protected $loggers = [];

    /**
     * @return Monolog
     * @throws Exception
     */
    protected function getDefaultLogger()
    {
        if (!array_key_exists($this->defaultName, $this->loggers)) {
            $logger = new Monolog($this->defaultName);

            $handler = new StreamHandler('php://stdout', env('LOG_LEVEL', 'DEBUG'));
            $handler->setFormatter($this);
            $logger->pushHandler($handler);

            $this->loggers[$this->defaultName] = $logger;
        }

        return $this->loggers[$this->defaultName];
    }

    /**
     * @param string|null $name
     * @return LoggerInterface
     * @throws Exception
     */
    public function getLogger(string $name = null)
    {
        if ($name === null) {
            return $this->getDefaultLogger();
        }

        if (!array_key_exists($name, $this->loggers)) {
            $this->loggers[$name] = $this->getDefaultLogger()->withName($name);
        }

        return $this->loggers[$name];
    }

    /**
     * message
     * level
     * context
     * channel
     * datetime
     * extra
     *
     * @param array $record
     * @return string
     */
    public function format(array $record)
    {
        $level = Monolog::getLevelName($record['level']);
        switch ($level) {
            case 'DEBUG':
            case 'INFO':
                $level = sprintf(static::ESCAPE, static::LIGHT + static::GRAY) . $level . sprintf(static::ESCAPE, static::RESET);
                break;

            case 'NOTICE':
            case 'WARNING':
                $level = sprintf(static::ESCAPE, static::LIGHT + static::YELLOW) . $level . sprintf(static::ESCAPE, static::RESET);
                break;

            case 'ERROR':
            case 'CRITICAL':
            case 'ALERT':
            case 'EMERGENCY':
                $level = sprintf(static::ESCAPE, static::LIGHT + static::RED) . $level . sprintf(static::ESCAPE, static::RESET);
                break;
        }

        // TODO: Move colors to a reusable attribute.
        $record['context'] = array_merge([
            'datetime' => $record['datetime']->format('Y-m-d H:i:s'),
            'channel' => $record['channel'],
            'level' => $level,
            'message' => $record['message'],

            'eol' => PHP_EOL,

            'lblack' => sprintf(static::ESCAPE, static::LIGHT + static::BLACK),
            'lred' => sprintf(static::ESCAPE, static::LIGHT + static::RED),
            'lgreen' => sprintf(static::ESCAPE, static::LIGHT + static::GREEN),
            'lyellow' => sprintf(static::ESCAPE, static::LIGHT + static::YELLOW),
            'lblue' => sprintf(static::ESCAPE, static::LIGHT + static::BLUE),
            'lmagenta' => sprintf(static::ESCAPE, static::LIGHT + static::MAGENTA),
            'lcyan' => sprintf(static::ESCAPE, static::LIGHT + static::CYAN),
            'lgray' => sprintf(static::ESCAPE, static::LIGHT + static::GRAY),

            'dblack' => sprintf(static::ESCAPE, static::DARK + static::BLACK),
            'dred' => sprintf(static::ESCAPE, static::DARK + static::RED),
            'dgreen' => sprintf(static::ESCAPE, static::DARK + static::GREEN),
            'dyellow' => sprintf(static::ESCAPE, static::DARK + static::YELLOW),
            'dblue' => sprintf(static::ESCAPE, static::DARK + static::BLUE),
            'dmagenta' => sprintf(static::ESCAPE, static::DARK + static::MAGENTA),
            'dcyan' => sprintf(static::ESCAPE, static::DARK + static::CYAN),
            'dgray' => sprintf(static::ESCAPE, static::DARK + static::GRAY),

            'reset' => sprintf(static::ESCAPE, static::RESET),
        ], $record['context']);

        return str_replace(
            array_map(function (string $key) {
                return "{{$key}}";
            }, array_keys($record['context'])),
            $record['context'],
            $record['extra']['format'] ?? $this->defaultFormat
        );
    }

    /**
     * @param array $records
     * @return array
     */
    public function formatBatch(array $records)
    {
        return array_map(function ($record) {
            return $this->format($record);
        }, $records);
    }
}
