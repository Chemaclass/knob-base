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

/**
 * Template singleton
 *
 * @author Jose Maria Valera Reales
 */
class MustacheRender
{
    const CACHE_FILE_MODE = 0660;
    const CACHE_LAMBDA_TEMPLATES = true;
    const CHARSET = 'UTF-8';
    const STRICT_CALLABLES = true;

    static $mustacheHelpersFile = 'mustache_helpers';
    static $mustacheParamsFile = 'mustache_params';
    static $templatesDir = 'templates';

    /**  @var array */
    private static $mustacheParams = null;

    /** @var \Mustache_Engine */
    protected $engine;

    /**
     * @param Mustache_Engine $engine
     */
    public function __construct(Mustache_Engine $engine)
    {
        $this->engine = $engine;
    }

    /**
     * @param string $templatePath path where is the template
     * @param array $args arguments to pass
     * @see \Mustache_Engine
     * @return string Rendered template
     */
    public function render($templatePath, array $args = [])
    {
        $args = array_merge($args, static::mustacheParams());

        return $this->engine->render($templatePath, $args);
    }

    /**
     * @return array
     */
    private static function mustacheParams()
    {
        if (null === static::$mustacheParams) {

            $baseParamsFile = VENDOR_KNOB_BASE_DIR . '/src/config/' . static::$mustacheParamsFile . '.php';
            $appParamsFile = APP_DIR . '/config/' . static::$mustacheParamsFile . '.php';

            $baseParams = (file_exists($baseParamsFile)) ? require $baseParamsFile : [];
            $appParams = (file_exists($appParamsFile)) ? require $appParamsFile : [];

            static::$mustacheParams = array_merge($baseParams, $appParams);
        }

        return static::$mustacheParams;
    }
}
