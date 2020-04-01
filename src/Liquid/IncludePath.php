<?php


namespace Liquid;

use vsconfig;

class IncludePath
{

    public static $stack = [];
    protected static $instance = null;

    public static function instance()
    {

        if (self::$instance === null) {
            self::$instance = new self();
        }

        return self::$instance;
    }


    public static function pushStack($key)
    {
        if (substr($key, -7) == '.liquid') {
            $key = basename($key, ".liquid");
        }
        self::$stack[] = $key;

        return self::instance();

    }

    public static function popStack()
    {
        array_pop(self::$stack);
        return self::instance();
    }
}
