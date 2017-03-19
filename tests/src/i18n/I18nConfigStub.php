<?php

namespace Tests\I18n;

use Knob\I18n\I18nConfig;

final class I18nConfigStub implements I18nConfig
{
    /** @var array */
    private $config;

    /**
     * @param array $config
     */
    public function __construct($config = [])
    {
        $this->config = $config;
    }

    public function globalLanguageFile()
    {
        return $this->config[self::DEFAULT_LANGUAGE_FILE];
    }

    public function availableLanguages()
    {
        return $this->config[self::AVAILABLE_LANGUAGES];
    }

    public function i18nLanguageDir()
    {
        return __DIR__;
    }

    public function languageBrowser()
    {
        return $this->globalLanguageFile();
    }

    public function languageValue($langKey)
    {
        if (!isset($this->config[self::AVAILABLE_LANGUAGES][$langKey])) {
            return $this->defaultLanguage();
        }

        return $this->config[self::AVAILABLE_LANGUAGES][$langKey];
    }

    public function defaultLanguage()
    {
        return $this->config[self::DEFAULT_LANGUAGE];
    }
}