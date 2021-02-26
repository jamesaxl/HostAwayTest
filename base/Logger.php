<?php

namespace app\base;

use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;

class Logger extends \Monolog\Logger
{
    private static array $loggers = [];

    /**
     *
     * constructor function
     *
     * @param string $key to define the type of log 'error or request'
     * @param string|null $config where to put the file log and level log
     */
    public function __construct(string $key = 'app', ?string $config = null)
    {
        parent::__construct($key);

        if (empty($config)) {
            $LOG_PATH = Config::get('LOG_PATH');
            $config = [
                'logFile' => "{$LOG_PATH}/{$key}.log",
                'logLevel' => \Monolog\Logger::DEBUG
            ];
        }

        $this->pushHandler(new StreamHandler($config['logFile'], $config['logLevel']));
    }

    /**
     *
     * retrieve log instance to log manually (option)
     *
     * @param string $key to define the type of log 'error or request'
     * @param string|null $config where to put the file log and level log
     * @return Logger
     */
    public static function getInstance(string $key = 'app', ?string $config = null): Logger
    {
        if (empty(self::$loggers[$key])) {
            self::$loggers[$key] = new Logger($key, $config);
        }

        return self::$loggers[$key];
    }

    /**
     *
     * Function to execute log
     *
     */
    public static function enableSystemLogs()
    {

        $LOG_PATH = Config::get('LOG_PATH');

        // Error Log
        self::$loggers['error'] = new Logger('errors');
        self::$loggers['error']->pushHandler(new StreamHandler("{$LOG_PATH}/errors.log"));
        ErrorHandler::register(self::$loggers['error']);

        // Request Log
        $data = [
            $_SERVER,
            $_REQUEST,
            trim(file_get_contents("php://input"))
        ];
        self::$loggers['request'] = new Logger('request');
        self::$loggers['request']->pushHandler(new StreamHandler("{$LOG_PATH}/request.log"));
        self::$loggers['request']->info("REQUEST", $data);
    }
}
