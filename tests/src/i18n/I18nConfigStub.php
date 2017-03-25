<?php declare(strict_types=1);

namespace Tests\I18n;

use Knob\I18n\I18nConfig;

final class I18nConfigStub implements I18nConfig
{
    /** @var array */
    private $config;

    public function __construct(array $config = [])
    {
        $this->config = $config;
    }

    public function globalLanguageFile(): string
    {
        return $this->config[self::DEFAULT_LANGUAGE_FILE];
    }

    public function availableLanguages(): array
    {
        return $this->config[self::AVAILABLE_LANGUAGES];
    }

    public function i18nLanguageDir(): string
    {
        return __DIR__;
    }

    public function languageBrowser(): string
    {
        return $this->globalLanguageFile();
    }

    public function languageValue(string $langKey): string
    {
        if (!isset($this->config[self::AVAILABLE_LANGUAGES][$langKey])) {
            return $this->defaultLanguage();
        }

        return $this->config[self::AVAILABLE_LANGUAGES][$langKey];
    }

    public function defaultLanguage(): string
    {
        return $this->config[self::DEFAULT_LANGUAGE];
    }
}