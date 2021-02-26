<?php

namespace app\base;

class Config
{
    // var where to load config
    private static $config;

    /**
     *
     * retrieve config
     *
     * @param string $key name of the key
     * @param mixed $default default value
     * @return mixed|null
     */
    public static function get(string $key, $default = null)
    {
        if (!self::$config) {
            self::$config = require_once(__DIR__.'/../config.php');
        }

        return !empty(self::$config[$key]) ? self::$config[$key] : $default;
    }
}