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
    use IsSingleton {
        IsSingleton::__construct as private __singletonConstruct;
    }

    const ESCAPE = "\033[%sm";

    const FOREGROUND = 0;
    const BACKGROUND = 10;

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

    const RESET = 0;

    /** @var string */
    protected $defaultName = 'default';

    /** @var string */
    protected $defaultFormat = '{dyellow}[{datetime}]{reset} {lblack}{channel}.{reset}{level}: {message}{eol}';

    /** @var string[] */
    protected $colors = [];

    /** @var Monolog[] */
    protected $loggers = [];

    protected function __construct()
    {
        $this->__singletonConstruct();

        $zones = [
            '' => static::FOREGROUND,
            'bg-' => static::BACKGROUND,
        ];

        $intensities = [
            'd' => static::DARK,
            'l' => static::LIGHT,
        ];

        $colors = [
            'black' => static::BLACK,
            'red' => static::RED,
            'green' => static::GREEN,
            'yellow' => static::YELLOW,
            'blue' => static::BLUE,
            'magenta' => static::MAGENTA,
            'cyan' => static::CYAN,
            'gray' => static::GRAY,
        ];

        foreach ($zones as $zone => $zoneValue) {
            foreach ($intensities as $intensity => $intensityValue) {
                foreach ($colors as $color => $colorValue) {
                    $this->colors[$zone . $intensity . $color] = sprintf(static::ESCAPE, $zoneValue + $intensityValue + $colorValue);
                }
            }
        }

        $this->colors['reset'] = sprintf(static::ESCAPE, static::RESET);
    }

    /**
     * @param string $format
     */
    public function setDefaultFormat(string $format)
    {
        $this->defaultFormat = $format;
    }

    /**
     * @return string
     */
    public function getDefaultFormat()
    {
        return $this->defaultFormat;
    }

    /**
     * @return Monolog
     * @throws Exception
     */
    protected function getDefaultLogger()
    {
        if (!array_key_exists($this->defaultName, $this->loggers)) {
            $logger = new Monolog($this->defaultName);

            $consoleLogLevel = env('eaw_log_level', 'DEBUG');
            $consoleHandler = new StreamHandler('php://stdout', $consoleLogLevel);
            $consoleHandler->setFormatter($this);
            $logger->pushHandler($consoleHandler);

            // If a log file is specified, add a file handler that strips out color codes.
            if (null !== $logFile = env('eaw_log_file_path')) {
                $fileLogLevel = env('eaw_log_file_level', $consoleLogLevel);
                $fileHandler = new StreamHandler($logFile, $fileLogLevel);
                $fileHandler->setFormatter($this);
                $fileHandler->pushProcessor(function ($record) {
                    // Replace all color codes with empty strings.
                    $record['context']  = array_merge(
                        $record['context'],
                        array_fill_keys(array_keys($this->colors), '')
                    );

                    return $record;
                });
                $logger->pushHandler($fileHandler);
            }

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

        $record['context'] = array_merge([
            'datetime' => $record['datetime']->format('Y-m-d H:i:s'),
            'channel' => $record['channel'],
            'level' => $level,
            'message' => $record['message'],
            'eol' => PHP_EOL,
        ], $this->colors, $record['context']);

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
