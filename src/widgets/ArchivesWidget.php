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

use Knob\Models\Archive;

/**
 *
 * @author José María Valera Reales
 */
class ArchivesWidget extends WidgetBase
{

    /**
     * (non-PHPdoc)
     *
     * @see \Widgets\WidgetBase::widget()
     */
    public function widget($args, $instance)
    {
        
        /*
         * Put the archives to show into the instance var.
         */
        $instance['archives'] = Archive::getMonthly();
        
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
