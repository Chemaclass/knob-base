<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knob\Libs;

/**
 * Resources for HTML things
 *
 * @author José María Valera Reales
 */
class Html
{

    /**
     * Remove "more" tag
     *
     * @param string $str
     * @return string
     */
    public static function removeReadMoreTag($str)
    {
        return str_replace('<!--more-->', '', $str);
    }
}