<?php

namespace Tests\I18n;

use Knob\I18n\I18n;
use Knob\I18n\MissingLangFileException;
use PHPUnit\Framework\TestCase;

final class I18nTest extends TestCase
{
    const LANG_VALUE = 'My Lang Test';
    const LANG_KEY = 'lang1';
    const DEFAULT_LANG = self::LANG_KEY;
    const DEFAULT_LANG_FILE = 'default';

    /**
     * @test
     */
    public function tryToTransANonExistingKey()
    {
        $i18n = $this->aI18n();
        $this->assertEquals(
            'non_existing_trans',
            $i18n->trans('non_existing_trans')
        );
    }

    /**
     * @return I18n
     */
    private function aI18n()
    {
        return new I18n(new I18nConfigStub([
            'availableLanguages' => [
                self::LANG_KEY => self::LANG_VALUE,
            ],
            'defaultLanguage' => self::DEFAULT_LANG,
            'defaultLanguageFile' => self::DEFAULT_LANG_FILE,
        ]));
    }

    /**
     * @test
     */
    public function transDefaultFile()
    {
        $i18n = $this->aI18n();
        $this->assertEquals(
            'my value from default',
            $i18n->trans('my_key')
        );
    }

    /**
     * @test
     */
    public function transWithFileNameAndKey()
    {
        $i18n = $this->aI18n();
        $this->assertEquals(
            'my value from other_file',
            $i18n->trans('other_file.my_key')
        );
    }

    /**
     * @test
     */
    public function forceLanguage()
    {
        $i18n = $this->aI18n();
        $this->assertEquals(
            'my value from default but lang2',
            $i18n->trans('my_key', [], 'lang2')
        );
    }

    /**
     * @test
     */
    public function missingLangFileButCallback()
    {
        $i18n = $this->aI18n();
        $this->assertEquals(
            'my value from other_file',
            $i18n->trans('other_file.my_key', [], 'lang2')
        );
    }

    /**
     * @test
     */
    public function nonExistingFile()
    {
        $this->expectException(MissingLangFileException::class);

        $i18n = $this->aI18n();
        $this->assertEquals(
            'whatever, because the file does not exists',
            $i18n->trans('non_existing_file.my_key')
        );
    }

    /**
     * @test
     */
    public function tryTo1ParamByString()
    {
        $i18n = $this->aI18n();
        $this->assertEquals(
            'my value',
            $i18n->trans('with_1_param [param:value]')
        );
    }

    /**
     * @test
     */
    public function tryTo2ParamByString()
    {
        $i18n = $this->aI18n();
        $this->assertEquals(
            'my value1 and value2',
            $i18n->trans('with_2_params [param1:value1, param2:value2]')
        );
    }

    /**
     * @test
     */
    public function tryTo1ParamByArray()
    {
        $i18n = $this->aI18n();
        $this->assertEquals(
            'my value',
            $i18n->trans('with_1_param', ['param' => 'value'])
        );
    }

    /**
     * @test
     */
    public function tryTo2ParamByArray()
    {
        $i18n = $this->aI18n();
        $this->assertEquals(
            'my value1 and value2',
            $i18n->trans('with_2_params', [
                'param1' => 'value1',
                'param2' => 'value2',
            ])
        );
    }

    public function tryToDefaultLanguageValue()
    {
        $i18n = $this->aI18n();
        $this->assertEquals(
            self::DEFAULT_LANG,
            $i18n->defaultLanguage()
        );
    }

    public function tryToLanguageValue()
    {
        $i18n = $this->aI18n();
        $this->assertEquals(
            self::LANG_VALUE,
            $i18n->langValue(self::LANG_KEY)
        );
    }
}