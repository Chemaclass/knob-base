<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Knob\Widgets;

use Knob\App;
use Mustache_Engine;
use Knob\I18n\I18n;
use Knob\Libs\Mustache\MustacheRender;

/**
 *
 * @author José María Valera Reales
 * @see https://codex.wordpress.org/Widgets_API
 */
abstract class WidgetBase extends \WP_Widget
{
    /**  @var string Title prefix from the Widget */
    static $titlePrefix = 'Knob ';

    /** @var string base directory-name from the templates */
    static $widgetTemplateDir = 'widget';

    /** @var string directory-name from the default templates inside the base template directory */
    static $widgetTemplateDirDefault = 'default';

    /** @var string Name file from the 'backend' template */
    static $backFileName = 'back';

    /** @var string Name file from the 'frontend' template */
    static $frontFileName = 'front';

    /** @var string */
    protected $className;

    /** @var MustacheRender */
    protected $mustacheRender;

    /** @var I18n */
    protected $i18n;

    /**
     * Each widget could implement his own isActive method, for example
     * in order to be active only for logged users.
     *
     * @return bool
     */
    public abstract function isActive();

    /**
     * @param Mustache_Engine $mustacheRender
     * @param string $id
     * @param string $title
     * @param array $widgetOps
     * @param array $controlOps
     *
     * @see https://developer.wordpress.org/reference/classes/wp_widget/__staticruct/
     */
    public function __construct(
        $id = '',
        $title = '',
        array $widgetOps = [],
        array $controlOps = []
    ) {
        $className = $this->getId();
        $className = substr($className, strrpos($className, '\\') + 1);
        $this->className = substr($className, 0, strpos($className, 'Widget'));

        $id = ($id && strlen($id)) ? $id : $this->className . '_Widget';
        $title = ($title && strlen($title)) ? $title : static::$titlePrefix . $this->className;
        $widgetOps = (count($widgetOps)) ? $widgetOps : [
            'classname' => strtolower($this->className) . '-widget',
            'description' => $this->className . ' widget',
        ];
        parent::__construct($id, $title, $widgetOps, $controlOps);
    }

    public function getId()
    {
        return get_called_class();
    }

    /**
     * Register the widget.
     */
    public function register()
    {
        $id = static::getId();
        if (!is_active_widget($id)) {
            register_widget($id);
        }
    }

    /**
     * Creating widget front-end.
     *
     * @see https://codex.wordpress.org/Widgets_API
     * @param $args
     * @param $instance
     */
    public function widget($args, $instance)
    {
        $this->mustacheRender = App::get(MustacheRender::class);
        $this->i18n = App::get(I18n::class);
        $instance['active'] = $this->isActive();
        echo $this->renderFrontendWidget($args, $instance);
    }

    /**
     * Widget Backend.
     *
     * @param unknown $instance
     *
     * @see https://codex.wordpress.org/Widgets_API
     */
    public function form($instance)
    {
        $fields = [
            'title',
        ];
        echo $this->renderBackendForm($instance, $fields);
    }

    /**
     * Updating widget replacing old instances with new.
     *
     * @param array $newInstance
     * @param array $oldInstance
     * @return array
     *
     * @see https://codex.wordpress.org/Widgets_API
     */
    public function update($newInstance, $oldInstance)
    {
        $instance = [];
        $instance['title'] = (!empty($newInstance['title']))
            ? strip_tags($newInstance['title']) : '';
        return $instance;
    }

    /**
     * Render backend form.
     *
     * @param array $instance
     * @param array $fields
     * @return string
     */
    protected function renderBackendForm($instance, array $fields)
    {
        /*
         * Prepare all names & ids
         */
        $fieldIds = [];
        $fieldNames = [];
        foreach ($fields as $f) {
            $fieldIds = array_merge($fieldIds, [
                $f => $this->get_field_id($f),
            ]);
            $fieldNames = array_merge($fieldNames, [
                $f => $this->get_field_name($f),
            ]);
        }
        $instance = array_merge($instance, [
            'fieldId' => $fieldIds,
            'fieldName' => $fieldNames,
        ]);

        return $this->mustacheRender->render(
            $this->getTemplateName(static::$backFileName),
            [
                'instance' => $instance,
            ]
        );
    }

    /**
     * Render fronted widget.
     *
     * @param array $args
     * @param array $instance
     * @return string
     */
    protected function renderFrontendWidget($args, $instance)
    {
        // Add the widget name.
        $instance['widgetName'] = $this->className;

        return $this->mustacheRender->render(
            $this->getTemplateName(static::$frontFileName),
            [
                'args' => $args,
                'instance' => $instance,
            ]
        );
    }

    /**
     * Create the template name string.
     *
     * @param string $fileName
     * @return string
     */
    protected function getTemplateName($fileName)
    {
        $pathFn = function ($dir) use ($fileName) {
            return static::$widgetTemplateDir . '/' . $dir . '/' . $fileName;
        };

        $pathToCheckFn = function ($path) {
            return APP_DIR . '/templates/' . $path . '.mustache';
        };

        $path = $pathFn($this->className);
        $pathToCheck = $pathToCheckFn($path);

        // If doesn't exists just take it by default from current APP
        if (!file_exists($pathToCheck)) {

            $path = $pathFn(static::$widgetTemplateDirDefault);
            $pathToCheck = $pathToCheckFn($path);

            // If doesn't exists take it by default from Knob-base
            if (!file_exists($pathToCheck)) {
                $path = '../../vendor/chemaclass/knob-base/src/templates/' . $path;
            }
        }

        return $path;
    }
}
