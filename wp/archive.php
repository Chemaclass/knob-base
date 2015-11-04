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
 * Archive.
 *
 * @link https://codex.wordpress.org/Creating_an_Archive_Index
 * @link https://codex.wordpress.org/Theme_Development
 */
$controller = new HomeController();
echo $controller->getArchive();
