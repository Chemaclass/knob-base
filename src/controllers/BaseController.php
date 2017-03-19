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

use Knob\App;
use Knob\I18n\I18n;
use Knob\Libs\Mustache\MustacheRender;
use Knob\Libs\Widgets;
use Knob\Models\User;
use Knob\Repository\UserRepository;
use Knob\Libs\Menus;

/**
 * @author José María Valera Reales
 */
abstract class BaseController
{
    /** @var User */
    protected $currentUser;

    /** @var MustacheRender */
    protected $mustacheRender;

    /** @var Widgets */
    protected $widgets;

    /** @var Menus */
    protected $menus;

    /** @var I18n */
    protected $i18n;

    public function __construct()
    {
        $this->i18n = App::get(I18n::class);
        $this->widgets = App::get(Widgets::class);
        $this->menus = App::get(Menus::class);
        $this->currentUser = App::get(UserRepository::class)->getCurrent();
        $this->mustacheRender = App::get(MustacheRender::class);
    }

    /**
     * head + template + footer
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
