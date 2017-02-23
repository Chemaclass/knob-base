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
use Knob\App;

/**
 * Search.
 *
 * @link https://codex.wordpress.org/Creating_a_Search_Page
 * @link https://codex.wordpress.org/Theme_Development
 */
$controller = new HomeController(
    App::get('i18n'),
    App::get('widgets'),
    App::get('menus')
);
echo $controller->getSearch();
