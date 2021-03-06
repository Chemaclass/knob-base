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
class SearcherWidget extends WidgetBase
{

    /*
     * All variables what we need are in Params.globalVars, and
     * these are autoimplement in every template.
     * So this widget is done :-)
     */
    
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
