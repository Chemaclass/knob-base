<?php
namespace Knob;

final class App
{
    private static $dependencies = [];

    /**
     * @return array
     */
    public static function allDependencies()
    {
        return static::$dependencies;
    }

    /**
     * @param string $key
     * @param mixed $value
     */
    public static function register($key, $value)
    {
        static::$dependencies[$key] = $value;
    }

    /**
     * @param string $key
     * @return mixed
     */
    public static function get($key)
    {
        return static::$dependencies[$key];
    }
}