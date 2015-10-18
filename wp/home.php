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
 * Home.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 * @link https://codex.wordpress.org/Theme_Development
 */
$controller = new HomeController();
$controller->getHome();
