<?php declare(strict_types=1);
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
use Knob\Libs\Menus;
use Knob\Libs\Mustache\MustacheRender;
use Knob\Libs\Widgets;
use Knob\Models\User;
use Knob\Repository\UserRepository;

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
     * @param string $templateName
     * @param array $templateVars
     * @return Response
     */
    public function renderPage(string $templateName, array $templateVars = []): Response
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
     * @return Response
     */
    public function render(
        string $templateName,
        array $templateVars = [],
        bool $addGlobalVariables = true
    ): Response {

        if ($addGlobalVariables) {
            $templateVars = array_merge($templateVars, $this->globalVariables());
        }

        return new Response(
            $this->mustacheRender->render($templateName, $templateVars)
        );
    }

    /**
     * Add the global variables for all controllers
     */
    public abstract function globalVariables(): array;

    private function wpHead(): string
    {
        ob_start();
        wp_head();
        return ob_get_clean();
    }

    private function wpFooter(): string
    {
        ob_start();
        wp_footer();
        return ob_get_clean();
    }
}
