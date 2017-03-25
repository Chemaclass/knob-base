<?php declare(strict_types=1);

namespace Knob\I18n;

interface I18nConfig
{
    const AVAILABLE_LANGUAGES = 'availableLanguages';
    const DEFAULT_LANGUAGE = 'defaultLanguage';
    const DEFAULT_LANGUAGE_FILE = 'defaultLanguageFile';

    const LANG_VALUE = 'english';
    const LANG_KEY = 'en';
    const DEFAULT_LANG = self::LANG_KEY;
    const DEFAULT_LANG_FILE = 'global';

    public function globalLanguageFile(): string;

    public function availableLanguages(): array;

    public function defaultLanguage(): string;

    public function i18nLanguageDir(): string;

    public function languageBrowser(): string;

    public function languageValue(string $langKey): string;
}