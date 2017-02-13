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

/**
 * Actions for Wordpress
 *
 * @author José María Valera Reales
 */
class Actions
{

    /**
     * Setup the actions
     */
    public static function setup()
    {
    }

    /**
     * Builds the definition for a single sidebar and returns the ID.
     * Call on "widgets_init" action.
     *
     * @see https://codex.wordpress.org/Function_Reference/register_sidebar
     * @see https://developer.wordpress.org/reference/hooks/widgets_init/
     *
     * @param array $activeWidgets /*
     *        List with your active widgets. Each item has to have:
     *        'id': His id. We'll use it later for get it and put in his correct place.
     *        'name': Sidebar name. Optional
     *        'classBeforeWidget': Class for 'beforeWidget'. Optional
     *        'beforeWidget': HTML to place before every widge. Optional
     *        'afterWidget': HTML to place after every widget. Optional
     *        'beforeTitle': HTML to place before every title. Optional
     *        'afterTitle': HTML to place after every title. Optional
     */
    public static function widgetsInit($activeWidgets = [])
    {
        if (!count($activeWidgets)) {
            return;
        }

        foreach ($activeWidgets as $w) {
            add_action('widgets_init',
                function () use ($w) {
                    if (isset($w['id'])) {
                        $name = isset($w['name']) ? $w['name'] : ucfirst(str_replace('_', ' ', $w['id']));
                        if (isset($w['beforeWidget'])) {
                            $beforeWidget = $w['beforeWidget'];
                        } else {
                            $classBeforeWidget = isset($w['classBeforeWidget']) ? $w['classBeforeWidget'] : str_replace('_', '-', $w['id']);
                            $beforeWidget = '<div class="widget ' . $classBeforeWidget . '">';
                        }
                        $afterWidget = isset($w['afterWidget']) ? $w['afterWidget'] : '</div>';
                        $beforeTitle = isset($w['beforeTitle']) ? $w['beforeTitle'] : '<span class="title">';
                        $afterTitle = isset($w['afterTitle']) ? $w['afterTitle'] : '</span>';

                        register_sidebar(
                            [
                                'id' => $w['id'],
                                'name' => $name,
                                'before_widget' => $beforeWidget,
                                'after_widget' => $afterWidget,
                                'before_title' => $beforeTitle,
                                'after_title' => $afterTitle
                            ]);
                    }
                });
        }
    }
}
