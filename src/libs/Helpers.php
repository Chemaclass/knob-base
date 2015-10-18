<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @param string|array $expression
 * @param string $tag
 */
function dd($expression, $tag = "Tag")
{
    echo '' . $tag . '<br>';
    var_dump($expression);
    exit();
}

/**
 * Cadena para debug
 *
 * @param string $str
 */
function debug($str)
{
    error_log(" DEBUG - " . $str);
}

/**
 * Cadena para info 'debug'
 *
 * @param string $str
 */
function info($str)
{
    error_log(" INFO - " . $str);
}