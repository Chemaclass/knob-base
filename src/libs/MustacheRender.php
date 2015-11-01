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
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;
use Mustache_Logger_StreamLogger;

/**
 * Template singleton
 *
 * @author José María Valera Reales
 */
class MustacheRender
{

    static $mustacheHelpersFile = 'mustache_helpers';

    static $mustacheParamsFile = 'mustache_params';

    static $templatesDir = 'templates';

    /*
     * Const
     */
    const CACHE_FILE_MODE = 0660;

    const CACHE_LAMBDA_TEMPLATES = true;

    const CHARSET = 'UTF-8';

    const STRICT_CALLABLES = true;

    /**
     * Singleton
     *
     * @var MustacheRender
     */
    private static $instance = null;

    /**
     *
     * @var array
     */
    private static $mustacheParams = null;

    /**
     *
     * @var \Mustache_Engine
     */
    protected $mustacheEngine = null;

    /**
     * Contructor
     */
    private function __construct()
    {
        $templatesFolder = static::getTemplatesFolderLocation();
        $this->mustacheEngine = new Mustache_Engine(
            [
                'charset' => static::CHARSET,
                'strict_callables' => static::STRICT_CALLABLES,
                'cache_file_mode' => static::CACHE_FILE_MODE,
                'cache_lambda_templates' => static::CACHE_LAMBDA_TEMPLATES,
                'loader' => new Mustache_Loader_FilesystemLoader($templatesFolder),
                'partials_loader' => new Mustache_Loader_FilesystemLoader($templatesFolder),
                'logger' => new Mustache_Logger_StreamLogger('php://stderr'),
                'helpers' => static::getHelpers(),
                'pragmas' => static::getPragmas(),
                'escape' => function ($value)
                {
                    return htmlspecialchars($value, ENT_COMPAT, static::CHARSET);
                }
            ]);
    }

    /**
     * Create a new instance if doesn't exists before.
     * Otherwise return the single instance.
     *
     * @see `Singleton pattern`
     * @return MustacheRender
     */
    public static function getInstance()
    {
        if (null == static::$instance) {
            static::$instance = new MustacheRender();
        }
        return static::$instance;
    }

    /**
     * Render a template
     *
     * @param string $templatePath path where is the template
     * @param array $args arguments to pass
     *
     * @see \Mustache_Engine
     *
     * @return string Rendered template
     */
    public function render($templatePath, array $args = [])
    {
        $args = array_merge($args, static::getMustacheParams());

        return $this->mustacheEngine->render($templatePath, $args);
    }

    /**
     * Return all params
     *
     * @return array<string,object>
     */
    protected static function getMustacheParams()
    {
        if (null == static::$mustacheParams) {

            $baseParamsFile = VENDOR_KNOB_BASE_DIR . '/src/config/' . static::$mustacheParamsFile . '.php';
            $appParamsFile = APP_DIR . '/config/' . static::$mustacheParamsFile . '.php';

            $baseParams = (file_exists($baseParamsFile)) ? require $baseParamsFile : [];
            $appParams = (file_exists($appParamsFile)) ? require $appParamsFile : [];

            static::$mustacheParams = array_merge($baseParams, $appParams);
        }
        return static::$mustacheParams;
    }

    /**
     * Return the relative path location where are the templates.
     *
     * @return string
     */
    private static function getTemplatesFolderLocation()
    {
        return str_replace('//', '/', APP_DIR . '/') . static::$templatesDir;
    }

    /**
     *
     * @return multitype:string
     */
    protected static function getPragmas()
    {
        return [
            Mustache_Engine::PRAGMA_FILTERS,
            Mustache_Engine::PRAGMA_BLOCKS
        ];
    }

    /**
     * List of helpers for our templates
     *
     * @return array<function>
     */
    protected static function getHelpers()
    {
        if (file_exists($pathHelpers = APP_DIR . '/config/' . static::$mustacheHelpersFile . '.php')) {
            $mustacheHelpersFile = include $pathHelpers;
        } else {
            $mustacheHelpersFile = [];
        }

        return array_merge(
            [
                'trans' => function ($value)
                {
                    return I18n::trans($value);
                },
                'transu' => function ($value)
                {
                    return I18n::transu($value);
                },
                'case' => [
                    'lower' => function ($value)
                    {
                        return strtolower((string) $value);
                    },
                    'upper' => function ($value)
                    {
                        return strtoupper((string) $value);
                    }
                ],
                'count' => function ($value)
                {
                    return count($value);
                },
                'moreThan1' => function ($value)
                {
                    return count($value) > 1;
                },
                'date' => [
                    'xmlschema' => function ($value)
                    {
                        return date('c', strtotime($value));
                    },
                    'string' => function ($value)
                    {
                        return date('l, d F Y', strtotime($value));
                    },
                    'format' => function ($value)
                    {
                        return date(get_option('date_format'), strtotime($value));
                    }
                ],
                'toArray' => function ($value)
                {
                    return explode(',', $value);
                },
                'ucfirst' => function ($value)
                {
                    return ucfirst($value);
                }
            ], $mustacheHelpersFile);
    }
}
