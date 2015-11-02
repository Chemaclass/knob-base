<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Knob\Models;

/**
 * Options from Wordpress
 *
 * @author José María Valera Reales
 */
class Option
{

    /**
     * Protected constructor to prevent creating a new instance of the
     * Option via the `new` operator from outside of this class.
     */
    protected function __construct()
    {
    }

    /**
     * Returns the value from an option name
     *
     * @param string option name to search
     *
     * @see https://codex.wordpress.org/Function_Reference/get_option
     * @see https://core.trac.wordpress.org/browser/tags/4.3.1/src/wp-includes/option.php#L27
     *
     * @return string Option value
     */
    public static function get($optionName, $defaultValue = false)
    {
        return get_option($optionName, $defaultValue);
    }

    /**
     * Save a new or update an option into the DB
     *
     * @param string $name Option name
     * @param string $value Option value
     * @param string $autoload Should this option be automatically loaded by the function
     *        wp_load_alloptions() (puts options into object cache on each page load)?
     * @param string $deprecated Deprecated in WordPress
     *
     * @see https://codex.wordpress.org/Function_Reference/add_option
     * @see https://codex.wordpress.org/Function_Reference/update_option
     *
     * @return bool False if option was not added and true if option was added
     */
    public static function save($name, $value, $autoload = true, $deprecated = '')
    {
        if (static::get($name)) {
            $return = update_option($name, $value, $autoload);
        } else {
            $return = add_option($name, $value, $deprecated, $autoload);
        }

        return $return;
    }

    /**
     * A safe way of removing a named option/value pair from the options database table.
     *
     * @param string $name Name of the option to be deleted.
     *
     * @see https://codex.wordpress.org/Function_Reference/delete_option
     *
     * @return bool True, if option is successfully deleted. False on failure, or option does not
     *         exist.
     */
    public static function delete($name)
    {
        return delete_option($option);
    }
}