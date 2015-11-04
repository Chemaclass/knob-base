<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
use Controllers\HomeController;

/**
 * Tag.
 *
 * @link https://codex.wordpress.org/Tag_Templates
 * @link https://codex.wordpress.org/Theme_Development
 */
$controller = new HomeController();
echo $controller->getTag();
