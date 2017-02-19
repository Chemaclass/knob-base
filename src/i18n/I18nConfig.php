<?php
namespace Knob\I18n;

interface I18nConfig
{
    const AVAILABLE_LANGUAGES = 'availableLanguages';
    const DEFAULT_LANGUAGE = 'defaultLanguage';
    const DEFAULT_LANGUAGE_FILE = 'defaultLanguageFile';

    /**
     * @return string
     */
    public function globalLanguageFile();

    /**
     * @return array
     */
    public function availableLanguages();

    /**
     * @return string
     */
    public function defaultLanguage();

    /**
     * @return string
     */
    public function i18nLanguageDir();

    /**
     * @return string
     */
    public function languageBrowser();

    /**
     * @param string $langKey
     * @return string
     */
    public function languageValue($langKey);
}