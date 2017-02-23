<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Knob\Controllers;

use Knob\Libs\MustacheRender;
use Models\User;

/**
 * Base Controller.
 *
 * @author José María Valera Reales
 */
abstract class BaseController
{

    /** @var MustacheRender */
    protected $mustacheRender;

    /** @var User */
    protected $currentUser;

    public function __construct()
    {
        $this->mustacheRender = MustacheRender::getInstance();
        $this->currentUser = User::getCurrent();
    }

    /**
     * Add the global variables for all controllers
     *
     * @return array
     */
    public abstract function getGlobalVariables();

    /**
     * @param string $templateName
     * @param array $templateVars
     * @param bool $addGlobalVariables
     * @return string
     */
    public function render($templateName, $templateVars = [], $addGlobalVariables = true)
    {
        if ($addGlobalVariables) {
            $templateVars = array_merge($templateVars, $this->getGlobalVariables());
        }
        return $this->mustacheRender->render($templateName, $templateVars);
    }

    /**
     * Print head + template + footer
     *
     * @param string $templateName Template name to print
     * @param array $templateVars Parameters to template
     * @return string
     */
    public function renderPage($templateName, $templateVars = [])
    {
        // HEAD
        ob_start();
        wp_head();
        $wpHead = ob_get_clean();

        // FOOTER
        ob_start();
        wp_footer();
        $wpFooter = ob_get_clean();

        return $this->render($templateName,
            array_merge($templateVars, [
                'wp_head' => $wpHead,
                'wp_footer' => $wpFooter,
            ]));
    }
}
