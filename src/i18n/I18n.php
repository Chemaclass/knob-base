<?php
/*
 * This file is part of the Knob-base package.
 *
 * (c) José María Valera Reales <chemaclass@outlook.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Knob\I18n;

/**
 *
 * Library who works with the i18n.
 *
 * @author José María Valera Reales
 */
class I18n
{
    const CURRENT_LANG = 'current-lang';
    const DEFAULT_LANG_KEY = 'en';
    const DEFAULT_LANG_VALUE = 'english';

    /** @var I18nConfig */
    private $config;

    /**
     * @param I18nConfig $config
     */
    public function __construct(I18nConfig $config)
    {
        $this->config = $config;
    }

    /**
     * Return the lang default
     *
     * @return string Language default
     */
    public function defaultLanguage()
    {
        return $this->config->defaultLanguage();
    }

    /**
     * Return a list with all languages available
     *
     * @return string[] names of directories available
     */
    public function availableLanguages()
    {
        return $this->config->availableLanguages();
    }

    /**
     * Return the fullname of the lang by current user.
     *
     * @param $lang string
     *
     * @return string Lang from the current user
     */
    public function langValue($lang)
    {
        return $this->config->languageValue($lang);
    }

    /**
     * Return the lang by current user if it was established
     *
     * @param string $forceLang
     * @return string Lang from the current user
     */
    public function getLangBrowserByCurrentUser($forceLang = '')
    {
        $allLangAvailable = $this->availableLanguages();
        $isLangAvailable = function ($langToCheck) use ($allLangAvailable) {
            return in_array($langToCheck, $allLangAvailable);
        };
        // we can force the lang
        if ($forceLang && $isLangAvailable($forceLang)) {
            return $forceLang;
        }
        // or set by session
        if (isset($_SESSION)
            && isset($_SESSION[self::CURRENT_LANG])
            && $isLangAvailable($_SESSION[self::CURRENT_LANG])
        ) {
            return $_SESSION[self::CURRENT_LANG];
        }

        $langBrowser = $this->config->languageBrowser();
        if ($isLangAvailable($langBrowser)) {
            return $langBrowser;
        }

        return $this->defaultLanguage();
    }

    /**
     * Return the fullName of the lang by current user.
     *
     * @param $forceLang boolean
     * @return string Lang from the current user
     */
    public function fullNameLanguageByCurrentUserBrowser($forceLang = false)
    {
        $lang = $this->getLangBrowserByCurrentUser($forceLang);

        return $this->langValue($lang);
    }

    /**
     * @return string[]
     */
    public function availableLanguagesKeyValue()
    {
        $languages = [];
        foreach ($this->availableLanguages() as $l) {
            $languages[] = [
                'key' => $l,
                'value' => $this->langValue($l)
            ];
        }

        /* Sort by key */
        usort($languages, function ($a, $b) {
            return strcasecmp($a['key'], $b['key']);
        });

        return $languages;
    }

    /**
     * Return the translation word by key
     *
     * @param string $toTranslate Key from the file
     * @param array $params Optional params
     * @param string $forceLang Force a language
     * @return string The translation
     */
    public function trans($toTranslate, $params = [], $forceLang = '')
    {
        $toTranslate = strtolower($toTranslate);
        $this->_getParams($toTranslate, $params);
        $toTranslate = trim($toTranslate);
        // If there isn't a dot we'll append one just to avoid the "undefined offset 1" warning
        if (false === strpos('.', $toTranslate)) {
            $toTranslate .= '.';
        }

        list($fileName, $key) = $this->parseFileKey($toTranslate);
        $langArray = $this->langFile($fileName, $forceLang);

        $value = isset($langArray[$key]) ? $langArray[$key] : $key;
        if (is_numeric(strpos($value, ':'))
            && !empty($params)
            && is_array($params)
        ) {
            $value = $this->_setParams($value, $params);
        }

        return $value;
    }

    /**
     * @param string $toTranslate
     * @return array
     */
    private function parseFileKey($toTranslate)
    {
        list($file, $key) = explode('.', $toTranslate);
        // Get the file called 'global' by default. Only if we didn't specify any file
        if (empty($key)) {
            $key = $file;
            $file = $this->config->globalLanguageFile();
        }

        return [$file, $key];
    }

    /**
     * Return the associative array (the file language)
     *
     * @param string $fileName Filename language
     * @param string $lang The 2 first chars of the language
     *
     * @return array
     * @throws MissingLangFileException
     */
    private function langFile($fileName = '', $lang = '')
    {
        if (empty($lang)) {
            $lang = $this->getLangBrowserByCurrentUser();
            if (!$lang) {
                $lang = $this->config->languageBrowser();
            }
        }

        $filePath = $this->langFilePath($lang, $fileName);

        if (!file_exists($filePath)) {
            if ($lang === $this->config->defaultLanguage()) {
                throw new MissingLangFileException($filePath);
            }
            return $this->langFile($fileName, $this->config->defaultLanguage());
        }

        return require $filePath;
    }

    /**
     * @param string $lang
     * @param string $file
     * @return string
     */
    private function langFilePath($lang, $file)
    {
        return $this->config->i18nLanguageDir() . "/$lang/$file.php";
    }

    /**
     * Set the params into the string
     *
     * @param string $value Input
     * @param array $params List of params
     * @return string
     */
    private function _setParams($value, $params)
    {
        $strFinal = $value;
        $key = '';
        for ($i = 0; $i < strlen($strFinal); $i++) {
            if ($strFinal[$i] == ':') { // 1º
                $_a = $i + 1;
                for ($j = $_a; $j < strlen($strFinal); $j++) {
                    $isLastOne = ($j == strlen($strFinal) - 1);
                    if (in_array($strFinal[$j],
                            [
                                ' ',
                                ',',
                                '\\',
                                '\'',
                                '"'
                            ]) || $isLastOne
                    ) { // 2º
                        $_b = $j;
                        $_b = ($isLastOne) ? $_b + 1 : $_b;
                        $key = substr($strFinal, $_a, $_b - $_a);
                        $i = $_b;
                        break;
                    }
                }

                // We found the key
                if (isset($params[$key])) {
                    $langKey = $params[$key];
                    $strFinalA = substr($strFinal, 0, $_a - 1);
                    $strFinalB = substr($strFinal, $_b);
                    $strFinal = $strFinalA . $langKey . $strFinalB;
                }
            }
        }
        return $strFinal;
    }

    /**
     * Format, if necessary, and translate the text with his parameters.
     * Put into &$params (2nd parameter) all possible parameters from the text $toTranslate.
     *
     * @param string $toTranslate Text to translate their parameters as "JSON".
     *        That array are identified as being in square brackets '[]'
     *        and each key / value pairs are separated by ':' and each element of a ','
     * @param array $params
     */
    private function _getParams(&$toTranslate, &$params)
    {
        /*
         * ( If the params-array is empty or it's an object)
         * And ( If the toTranslate-string contain '[' where would be the parameters )
         */
        if (((is_array($params) && empty($params)) || is_object($params))
            && ($pos = strpos($toTranslate, '['))
        ) {
            $params = [];
            // +1 and -1 => to remove the brackets '[]'
            $strParams = substr($toTranslate, $pos + 1, -1);
            $toTranslate = substr($toTranslate, 0, $pos);
            // Split by a comma the parameters
            $strParams = preg_replace('/\s+/', '', $strParams);
            $_params = explode(',', $strParams);
            foreach ($_params as $value) {
                list($k, $v) = explode(':', $value);
                $params[$k] = $v;
            }
        }
    }

    /**
     * Return the translated word with the first letter in uppercase.
     *
     * @param string $key Language key file.
     * @param array $params optional parameters.
     * @param string $forceLang optional lang to force.
     * @return string Value translated.
     */
    public function transU($key, $params = [], $forceLang = '')
    {
        return ucfirst($this->trans($key, $params, $forceLang));
    }

    /**
     * @param $arg
     * @return string
     */
    public function substr($arg)
    {
        list($string, $len) = explode(' ', $arg);

        return empty($len) ? $string : substr($string, 0, $len);
    }
}
