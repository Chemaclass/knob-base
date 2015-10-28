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

use Knob\Models\Archive;
use Knob\Models\Post;
use Knob\Models\Term;
use Knob\Models\User;
use Knob\Libs\Template;
use Knob\Libs\WalkerNavMenu;
use Knob\Libs\Utils;

/**
 * Base Controller.
 *
 * @author José María Valera Reales
 */
abstract class BaseController
{

    private $mustacheParams = [];

    protected $currentUser = null;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->mustacheParams = Utils::getMustacheParams();
        $this->currentUser = User::getCurrent();
    }

    /**
     * Return the template
     */
    public abstract function getTemplate();

    /**
     * Add the global variables for all controllers
     *
     * @return array $templateVars
     */
    public function getGlobalVariables()
    {
        $globalVars = [];
        return array_merge($this->mustacheParams, $globalVars);
    }

    /**
     * Render a partial
     *
     * @param string $templateName
     * @param array $templateVars
     */
    public function render($templateName, $templateVars = [], $addGlobalVariables = true)
    {
        if ($addGlobalVariables) {
            $templateVars = array_merge($templateVars, $this->getGlobalVariables());
        }
        return $this->getTemplate()->render($templateName, $templateVars);
    }

    /**
     * Print head + template + footer
     *
     * @param string $templateName Template name to print
     * @param array $templateVars Parameters to template
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

        echo $this->render($templateName,
            array_merge($templateVars, [
                'wp_head' => $wpHead,
                'wp_footer' => $wpFooter
            ]));
    }
}
