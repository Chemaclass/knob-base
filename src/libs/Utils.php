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

use Knob\I18n\I18nConfig;

/**
 * Class with Utilities
 *
 * @author José María Valera Reales
 */
class Utils implements I18nConfig
{
    const CONFIG_FILE = 'config';
    const PARAMETERS_FILE = 'parameters';

    const TYPE_TAG = 'tag';
    const TYPE_CATEGORY = 'category';
    const TYPE_SEARCH = 'search';
    const TYPE_AUTHOR = 'author';

    /** @var array */
    private $i18nConfig;
    /** @var string */
    private $appDir;
    /** @var array|null */
    private $parameters = null;
    /** @var array|null */
    private $config = null;

    /**
     * @param string $appDir
     * @param array $i18nConfig
     */
    public function __construct($appDir, array $i18nConfig)
    {
        $this->appDir = $appDir;
        $this->i18nConfig = $i18nConfig;
    }

    public function config()
    {
        return $this->getConfigFile();
    }

    public function i18nLanguageDir()
    {
        return $this->appDir . '/i18n/';
    }

    public function languageBrowser()
    {
        return $this->globalLanguageFile();
    }

    public function globalLanguageFile()
    {
        return $this->i18nConfig[self::DEFAULT_LANGUAGE_FILE];
    }

    public function availableLanguages()
    {
        return $this->i18nConfig[self::AVAILABLE_LANGUAGES];
    }

    public function defaultLanguage()
    {
        return $this->i18nConfig[self::DEFAULT_LANGUAGE];
    }

    public function languageValue($langKey)
    {
        if (!isset($this->i18nConfig[self::AVAILABLE_LANGUAGES][$langKey])) {
            return $this->defaultLanguage();
        }

        return $this->i18nConfig[self::AVAILABLE_LANGUAGES][$langKey];
    }

    /**
     * Return all parameters values
     *
     * @return array
     */
    protected function getParametersFile()
    {
        $file_name = $this->appDir . '/config/' . self::PARAMETERS_FILE . '.php';
        if (!file_exists($file_name)) {
            return [];
        }

        if (null === $this->parameters) {
            foreach (require($file_name) as $k => $v) {
                $this->parameters["%{$k}%"] = $v;
            }
        }
        return $this->parameters;
    }

    /**
     * Return all global config values
     */
    public function getConfigFile()
    {
        $configPath = $this->appDir . '/config/' . self::CONFIG_FILE . '.php';
        if (!file_exists($configPath)) {
            // Internal base config file
            $configPath = VENDOR_KNOB_BASE_DIR . '/src/config/' . self::CONFIG_FILE . '.php';
        }

        if (null === $this->config && file_exists($configPath)) {
            $this->config = require($configPath);
            static::replaceParameters($this->config);
        }

        return $this->config;
    }

    /**
     * Replace all possible parameters from config/parameters to config/config files
     *
     * @param array $configOptions Reference of config options
     */
    private static function replaceParameters(&$configOptions)
    {
        if (!$params = static::getParametersFile()) {
            return;
        }

        foreach ($configOptions as $configKey => &$configItem) {
            // Check if it's an array
            if (is_array($configItem)) {
                static::replaceParameters($configItem);
            } elseif (is_string($configItem)) {
                // check if the str contain %...%
                if (substr_count($configItem, '%') >= 2) {
                    $configItem = $params[$configItem];
                }
            }
        }
    }

    /**
     * Check the value: not only spaces, with value and more than 0.
     *
     * @param string $value String to check.
     * @return boolean true: valid, false: not valid.
     */
    public static function isValidStr($value)
    {
        return (isset($value) && !ctype_space($value) && strlen($value) > 0);
    }

    /**
     * Return the attachment ID from his url
     *
     * @param string $attachmentUrl URL del attachment
     * @return integer ID del attachment
     */
    public static function getAttachmentIdFromUrl($attachmentUrl = '')
    {
        if (empty($attachmentUrl)) {
            return;
        }
        global $wpdb;
        $attachmentId = false;
        // Get the upload directory paths
        $upload_dir_paths = wp_upload_dir();
        // Make sure the upload path base directory exists in the attachment URL,
        // to verify that we're working with a media library image
        if (false !== strpos($attachmentUrl, $upload_dir_paths['baseurl'])) {
            // If this is the URL of an auto-generated thumbnail, get the URL of the original image
            $attachmentUrl = preg_replace('/-\d+x\d+(?=\.(jpg|jpeg|png|gif)$)/i', '', $attachmentUrl);
            // Remove the upload path base directory from the attachment URL
            $attachmentUrl = str_replace($upload_dir_paths['baseurl'] . '/', '', $attachmentUrl);
            // Finally, run a custom database query to get the attachment ID from the modified
            // attachment URL
            $attachmentId = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT wposts.ID
					FROM {$wpdb->posts} wposts, {$wpdb->postmeta} wpostmeta
					WHERE wposts.ID = wpostmeta.post_id
					AND wpostmeta.meta_key = '_wp_attached_file'
					AND wpostmeta.meta_value = '%s'
					AND wposts.post_type = 'attachment'", $attachmentUrl));
        }
        return $attachmentId;
    }

    /**
     * Return the current lang of the browerser
     *
     * @return string Just the first two chars. Ex: de, es, en, fr
     */
    public static function getLangBrowser()
    {
        return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
    }

    /**
     * Return $_SERVER[ REQUEST_URI ]
     *
     * @return string
     */
    public static function getRequestUri()
    {
        return $_SERVER[REQUEST_URI];
    }

    /**
     *
     * @param string $str
     * @param number $cant
     * @param string $separator
     * @return string
     */
    public static function getWordsByStr($str, $cant = 8, $separator = ' ')
    {
        // Generate an array from the str cut by the separator
        $words = explode($separator, $str, $cant + 1);
        $numWords = count($words);
        // remove all empty values
        $filteredWords = array_filter($words, 'strlen');
        $numWordsFiltradas = count($filteredWords);
        // if they're a different number of words that mean something was filtered
        if ($numWordsFiltradas != $numWords) {
            $cant -= ($numWords - $numWordsFiltradas);
            $words = $filteredWords;
        }
        // if the content it's longer than the excerpt put '...'
        if (count($words) > $cant) {
            array_pop($words);
            $words[] = '...';
        }
        return implode($separator, $words);
    }


    /**
     * Slugify
     *
     * @param string $str
     */
    public static function slugify($str)
    {
        $str = preg_replace('/[^a-z0-9 -]+/', '', strtolower($str));

        return trim(str_replace(' ', '-', $str), '-');
    }
}
