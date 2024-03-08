<?php

namespace Shishima\TranslateSpreadsheet\Strategy\Translate;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use Illuminate\Support\Sleep;

class GeminiTranslateStrategy extends TranslateAbstract
{
    private $startTime;
    private $rateLimit;

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function doTranslate($currentSheetInstance, $transTarget)
    {
        if (! class_exists(\Gemini\Laravel\Facades\Gemini::class))
        {
            throw new \Exception('The google-gemini-php/laravel package is not present. Please ensure that you install this package before utilizing it.');
        }

        $this->startTime = now();

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
                    $this->rateLimit++;
                    $textTranslated = $this->translateText($value, $transTarget);

                    $clonedWorksheet->setCellValue($cellCoordinate, $textTranslated);

                    $this->handleRateLimit();
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
        $result = \Gemini\Laravel\Facades\Gemini::geminiPro()->generateContent($this->getTranslatePrompt($value, $transTarget));
        $text   = $this->unwrap($result->text());
        $this->debugLogTextTranslated($value, $text);

        return $text;
    }

    /**
     * @throws \Exception
     */
    public function getTranslatePrompt(string $value, string $transTarget): string
    {
        $language = Arr::get(config('translate-spreadsheet.mapping_abbreviation_language'), $transTarget);
        if (empty($language))
        {
            throw new \Exception('Translate target mapping not found!');
        }

        return "Translate to $language: '$value'";
    }

    public function unwrap($value)
    {
        foreach (config('translate-spreadsheet.gemini.unwrap_character') as $needle)
        {
            if (Str::startsWith($value, $needle))
            {
                $value = Str::substr($value, Str::length($needle));
            }

            if (Str::endsWith($value, $needle))
            {
                $value = Str::substr($value, 0, -Str::length($needle));
            }
        }

        return $value;
    }

    public function isOverLimitTime(): bool
    {
        $currentDt = now();
        return $currentDt->diffInSeconds($this->startTime) > 60;
    }

    public function handleRateLimit(): void
    {
        if ($this->isOverLimitTime())
        {
            if ($this->rateLimit > config('translate-spreadsheet.gemini.rate_limit'))
            {
                Sleep::for(60)->second();
            }

            $this->rateLimit = 0;
            $this->startTime = now();
        }
    }
}
