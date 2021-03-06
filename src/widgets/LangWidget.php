<?php
/*
 * This file is part of the Knob-mvc package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Knob\Widgets;

use Knob\App;
use Knob\I18n\I18n;

/**
 *
 * @author José María Valera Reales
 */
class LangWidget extends WidgetBase
{

    /**
     * (non-PHPdoc)
     *
     * @see \Widgets\WidgetBase::widget()
     */
    public function widget($args, $instance)
    {

        /*
         * Put all languages available to show into the instance var.
         */
        $instance['languages'] = App::get(I18n::class)
            ->availableLanguagesKeyValue();

        /*
         * And call the widget func from the parent class WidgetBase.
         */
        parent::widget($args, $instance);
    }

    /**
     * (non-PHPdoc)
     *
     * @see \Widgets\WidgetBase::isActive()
     */
    public function isActive()
    {
        return true;
    }
}
