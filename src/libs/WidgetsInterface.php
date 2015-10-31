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
 * Actions for Wordpress
 *
 * @author José María Valera Reales
 */
interface WidgetsInterface
{

    static function getDinamicSidebarActive();

    static function setup();
}
