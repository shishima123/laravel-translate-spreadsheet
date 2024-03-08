<?php

namespace Shishima\TranslateSpreadsheet\Strategy\Translate;

use Shishima\TranslateSpreadsheet\TranslateSpreadsheet;

abstract class TranslateAbstract
{
    protected TranslateSpreadsheet $translateSpreadSheetInstance;

    abstract public function doTranslate($currentSheetInstance, string $transTarget);

    abstract public function translateText($value, $transTarget, $transSource = ''): ?string;

    public function setTranslateSpreadSheetInstance($translateSpreadSheetInstance)
    {
        return $this->translateSpreadSheetInstance = $translateSpreadSheetInstance;
    }

    public function debugLogTextTranslated($value, $text): void
    {
        if ($this->translateSpreadSheetInstance->isEnableDebug())
        {
            logger(sprintf("[%s]-->[%s]", $value, $text));
        }
    }
}
