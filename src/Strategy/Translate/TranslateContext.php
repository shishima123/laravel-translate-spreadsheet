<?php

namespace Shishima\TranslateSpreadsheet\Strategy\Translate;

use PhpOffice\PhpSpreadsheet\Exception;
use Shishima\TranslateSpreadsheet\Enumerations\TranslateEngine;

class TranslateContext
{
    private TranslateAbstract $strategy;

    public function __construct(TranslateEngine $translateEngine)
    {
        $this->strategy = match ($translateEngine)
        {
            TranslateEngine::Gemini => new GeminiTranslateStrategy(),
            default => new GoogleTranslateStrategy(),
        };
    }

    /**
     * @throws Exception
     */
    public function doTranslate($currentSheetInstance, $translateSpreadSheetInstance, $transTarget)
    {
        $this->strategy->setTranslateSpreadSheetInstance($translateSpreadSheetInstance);
        return $this->strategy->doTranslate($currentSheetInstance, $transTarget);
    }
}
