<?php

namespace Knob\I18n;

class MissingLangFileException extends \Exception
{
    const MESSAGE = 'Missing lang file: ';

    /**
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        parent::__construct(self::MESSAGE . $filePath);
    }
}