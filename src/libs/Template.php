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
use Knob\Config\Params;
use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;
use Mustache_Logger_StreamLogger;

/**
 * Template singleton
 *
 * @author José María Valera Reales
 */
class Template
{

    static $mustacheHelpersFile = 'mustache_helpers';

    static $templatesDir = 'templates';

    /*
     * Const
     */
    const CACHE_FILE_MODE = 0660;

    const CACHE_LAMBDA_TEMPLATES = true;

    const CHARSET = 'UTF-8';

    const STRICT_CALLABLES = true;

    /*
     * Singleton
     */
    private static $instance = null;

    /*
     * Members
     */
    protected $renderEngine = null;

    /**
     * Constructor
     */
    private function __construct()
    {
        /*
         * Render Engine.
         */
        $templatesFolder = static::getTemplatesFolderLocation();
        $this->renderEngine = new Mustache_Engine(
            [
                'charset' => static::CHARSET,
                'strict_callables' => static::STRICT_CALLABLES,
                'cache_file_mode' => static::CACHE_FILE_MODE,
                'cache_lambda_templates' => static::CACHE_LAMBDA_TEMPLATES,
                'loader' => new Mustache_Loader_FilesystemLoader($templatesFolder),
                'partials_loader' => new Mustache_Loader_FilesystemLoader($templatesFolder),
                'logger' => new Mustache_Logger_StreamLogger('php://stderr'),
                'helpers' => self::getHelpers(),
                'pragmas' => self::getPragmas(),
                'escape' => function ($value)
                {
                    return htmlspecialchars($value, ENT_COMPAT, static::CHARSET);
                }
            ]);
    }

    /**
     *
     * @return NULL
     */
    public static function getInstance()
    {
        if (!static::$instance) {
            static::$instance = new Template();
        }
        return static::$instance;
    }

    /**
     *
     * @return \Mustache_Engine
     */
    public function getRenderEngine()
    {
        return $this->renderEngine;
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
    protected function getPragmas()
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
    protected function getHelpers()
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
