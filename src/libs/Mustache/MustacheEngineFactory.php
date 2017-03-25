<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) Jose Maria Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Knob\Libs\Mustache;

use Mustache_Engine;
use Mustache_Loader_FilesystemLoader;
use Mustache_Logger_StreamLogger;

/**
 * Template singleton
 *
 * @author Jose Maria Valera Reales
 */
class MustacheEngineFactory
{

    const CACHE_FILE_MODE = 0660;
    const CACHE_LAMBDA_TEMPLATES = true;
    const CHARSET = 'UTF-8';
    const STRICT_CALLABLES = true;

    const HELPERS_FILE = 'mustache_helpers';
    const PARAMS_FILE = 'mustache_params';
    const TEMPLATES_DIR = 'templates';

    /** @var \Mustache_Engine */
    protected $mustacheEngine = null;

    /**
     * @return Mustache_Engine
     */
    public function createMustacheEngine()
    {
        $templatesFolder = static::getTemplatesFolderLocation();

        return new Mustache_Engine([
            'charset' => static::CHARSET,
            'strict_callables' => static::STRICT_CALLABLES,
            'cache_file_mode' => static::CACHE_FILE_MODE,
            'cache_lambda_templates' => static::CACHE_LAMBDA_TEMPLATES,
            'loader' => new Mustache_Loader_FilesystemLoader($templatesFolder),
            'partials_loader' => new Mustache_Loader_FilesystemLoader($templatesFolder),
            'logger' => new Mustache_Logger_StreamLogger('php://stderr'),
            'helpers' => $this->helpers(),
            'pragmas' => $this->pragmas(),
            'escape' => function ($value) {
                return htmlspecialchars($value, ENT_COMPAT, static::CHARSET);
            },
        ]);
    }

    /**
     * Return the relative path location where are the templates.
     *
     * @return string
     */
    private function getTemplatesFolderLocation()
    {
        return str_replace('//', '/', APP_DIR . '/') . self::TEMPLATES_DIR;
    }

    /**
     * List of helpers for our templates
     *
     * @return array
     */
    private function helpers()
    {
        $HELPERS_FILE = [];
        // From base
        if (file_exists($pathHelpers = VENDOR_KNOB_BASE_DIR . '/src/config/' . static::HELPERS_FILE . '.php')) {
            $HELPERS_FILE += include $pathHelpers;
        }
        // From App
        if (file_exists($pathHelpers = APP_DIR . '/config/' . static::HELPERS_FILE . '.php')) {
            $HELPERS_FILE += include $pathHelpers;
        }

        return array_merge([
            'case' => [
                'lower' => function ($value) {
                    return strtolower((string)$value);
                },
                'upper' => function ($value) {
                    return strtoupper((string)$value);
                },
            ],
            'count' => function ($value = []) {
                return count($value);
            },
            'moreThan1' => function ($value = []) {
                return count($value) > 1;
            },
            'date' => [
                'xmlschema' => function ($value) {
                    return date('c', strtotime($value));
                },
                'string' => function ($value) {
                    return date('l, d F Y', strtotime($value));
                },
                'format' => function ($value) {
                    return date(get_option('date_format'), strtotime($value));
                },
            ],
            'toArray' => function ($value) {
                return explode(',', $value);
            },
            'ucfirst' => function ($value) {
                return ucfirst($value);
            },
        ], $HELPERS_FILE);
    }

    /**
     * @return string[]
     */
    private function pragmas()
    {
        return [
            Mustache_Engine::PRAGMA_FILTERS,
            Mustache_Engine::PRAGMA_BLOCKS,
        ];
    }
}
