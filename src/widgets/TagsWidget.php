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

/**
 *
 * @author José María Valera Reales
 */
class TagsWidget extends WidgetBase
{

    /**
     * (non-PHPdoc)
     *
     * @see \Widgets\WidgetBase::widget()
     */
    public function widget($args, $instance)
    {

        /*
         * Put the tags to show into the instance var.
         */
        $instance['tags'] = []; //Term::getTags();

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
