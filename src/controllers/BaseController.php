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

use Knob\I18n\I18n;
use Knob\Libs\MenusInterface;
use Knob\Libs\MustacheRender;
use Knob\Libs\WidgetsInterface;
use Models\User;

/**
 * Base Controller.
 *
 * @author José María Valera Reales
 */
abstract class BaseController
{
    /** @var WidgetsInterface */
    protected $widgets;

    /** @var MenusInterface */
    protected $menus;

    /** @var I18n */
    protected $i18n;

    /** @var MustacheRender */
    protected $mustacheRender;

    /** @var User */
    protected $currentUser;

    /**
     * @param I18n $i18n
     * @param WidgetsInterface $widgets
     * @param MenusInterface $menus
     */
    public function __construct(I18n $i18n, WidgetsInterface $widgets, MenusInterface $menus)
    {
        $this->i18n = $i18n;
        $this->widgets = $widgets;
        $this->menus = $menus;

        $this->mustacheRender = MustacheRender::getInstance();
        $this->currentUser = User::getCurrent();
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
        return $this->render($templateName,
            array_merge($templateVars, [
                'wp_head' => $this->wpHead(),
                'wp_footer' => $this->wpFooter(),
            ])
        );
    }

    /**
     * @param string $templateName
     * @param array $templateVars
     * @param bool $addGlobalVariables
     * @return string
     */
    public function render($templateName, $templateVars = [], $addGlobalVariables = true)
    {
        if ($addGlobalVariables) {
            $templateVars = array_merge($templateVars, $this->globalVariables());
        }

        return $this->mustacheRender->render($templateName, $templateVars);
    }

    /**
     * Add the global variables for all controllers
     *
     * @return array
     */
    public abstract function globalVariables();

    /**
     * @return string
     */
    private function wpHead()
    {
        ob_start();
        wp_head();
        return ob_get_clean();
    }

    /**
     * @return string
     */
    private function wpFooter()
    {
        ob_start();
        wp_footer();
        return ob_get_clean();
    }
}
