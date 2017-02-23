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

use Knob\I18n\I18n;

/**
 * Filters from WordPress
 *
 * @author José María Valera Reales
 */
class Filters
{
    /**  @var I18n */
    private $i18n;

    /**
     * @param I18n $i18n
     */
    public function __construct(I18n $i18n)
    {
        $this->i18n = $i18n;
        $this->showAdminBar(false);
        $this->navMenuCssClass();
    }

    /**
     * Set the display status of the Toolbar for the front side of your website (you cannot turn off
     * the toolbar on the WordPress dashboard).
     *
     * @link https://codex.wordpress.org/Function_Reference/show_admin_bar
     */
    protected static function showAdminBar($flag = false)
    {
        show_admin_bar($flag);
    }

    /**
     * A filter hook called by the WordPress Walker_Nav_Menu class.
     *
     * @see http://codex.wordpress.org/Plugin_API/Filter_Reference/nav_menu_css_class
     */
    protected static function navMenuCssClass()
    {
        add_filter('nav_menu_css_class', function ($classes, $item) {
            // if (is_single() && $item->title == "Blog") { // Notice you can change the
            // conditional from is_single() and $item->title
            $classes[] = "dropdown";
            // }
            return $classes;
        }, 10, 2);
    }
}
