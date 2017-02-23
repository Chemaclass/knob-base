<?php
namespace Knob;

final class App
{
    static private $dependencies = [];

    /**
     * @param string $key
     * @param mixed $value
     */
    public static function register($key, $value)
    {
        static::$dependencies[$key] = $value;
    }

    public static function get($key)
    {
        return static::$dependencies[$key];
    }
}