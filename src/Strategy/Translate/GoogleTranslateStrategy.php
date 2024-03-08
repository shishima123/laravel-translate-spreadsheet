<?php

namespace Shishima\TranslateSpreadsheet\Strategy\Translate;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use Stichoza\GoogleTranslate\GoogleTranslate;

class GoogleTranslateStrategy extends TranslateAbstract
{
    /**
     * @throws Exception
     * @throws \Exception
     */
    public function doTranslate($currentSheetInstance, $transTarget)
    {
        $clonedWorksheet = clone $currentSheetInstance;
        $clonedWorksheet->setTitle($this->translateSpreadSheetInstance->getTitle($currentSheetInstance->getTitle(), $transTarget, new self()));
        $highestRow         = $clonedWorksheet->getHighestRow(); // e.g. 10
        $highestColumn      = $clonedWorksheet->getHighestColumn(); // e.g 'F'
        $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn); // e.g. 5

        for ($row = 1; $row <= $highestRow; ++$row)
        {
            for ($col = 1; $col <= $highestColumnIndex; ++$col)
            {
                $cellCoordinate = Coordinate::stringFromColumnIndex($col).$row;
                $value          = trim($clonedWorksheet->getCell($cellCoordinate)->getValue());
                if ($value && $this->translateSpreadSheetInstance->isNonEmptyStr($value) && $this->translateSpreadSheetInstance->isNonNumeric($value))
                {
                    $textTranslated = $this->translateText($value, $transTarget, $this->translateSpreadSheetInstance->getTransSource());
                    $clonedWorksheet->setCellValue($cellCoordinate, $textTranslated);
                }
            }
        }
        return $clonedWorksheet;
    }

    /**
     * @throws \Exception
     */
    public function translateText($value, $transTarget, $transSource = ''): ?string
    {
        $text = GoogleTranslate::trans(string: $value, target: $transTarget, source: $transSource);
        $this->debugLogTextTranslated($value, $text);
        return $text;
    }
}
