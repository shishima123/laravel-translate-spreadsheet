<?php

namespace Shishima\TranslateSpreadsheet;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWrite;
use Stichoza\GoogleTranslate\Exceptions\LargeTextException;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;
use Stichoza\GoogleTranslate\Exceptions\TranslationRequestException;
use Stichoza\GoogleTranslate\GoogleTranslate;

class TranslateSpreadsheet
{
    public string|null $transTarget = null;

    public string|null $transSource = null;

    public bool|null $shouldRemoveSheet;

    /**
     * @throws LargeTextException
     * @throws Exception
     * @throws RateLimitException
     * @throws TranslationRequestException
     * @throws \PhpOffice\PhpSpreadsheet\Writer\Exception
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception
     * @throws \Exception
     */
    public function translate($file): string
    {
        $file = $this->setFile($file);

        $reader      = new XlsxReader();
        $spreadsheet = $reader->load($file->getPathname());
        $sheetNames  = $spreadsheet->getSheetNames();

        foreach ($sheetNames as $index => $sheetName)
        {
            $clonedWorksheet = clone $spreadsheet->getSheetByName($sheetName);
            $clonedWorksheet->setTitle($sheetName.'_'.Str::upper($this->getTransTarget()));
            $highestRow         = $clonedWorksheet->getHighestRow(); // e.g. 10
            $highestColumn      = $clonedWorksheet->getHighestColumn(); // e.g 'F'
            $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn); // e.g. 5

            for ($row = 1; $row <= $highestRow; ++$row)
            {
                for ($col = 1; $col <= $highestColumnIndex; ++$col)
                {
                    $cellCoordinate = Coordinate::stringFromColumnIndex($col).$row;
                    $value          = $clonedWorksheet->getCell($cellCoordinate)->getValue();
                    if ($value)
                    {
                        $textTranslated = GoogleTranslate::trans(string: $value, target: $this->getTransTarget(), source: $this->getTransSource());
                        $clonedWorksheet->setCellValue($cellCoordinate, $textTranslated);
                    }
                }
            }
            $spreadsheet->addSheet($clonedWorksheet, $index * 2 + 1);
        }

        if ($this->getShouldRemoveSheet())
        {
            $this->removeOldSheetAfterTranslate(sheetList: $sheetNames, spreadsheet: $spreadsheet, type: 'sheet_name');
        }

        $output = $this->setOutput($file);
        $writer = new XlsxWrite($spreadsheet);
        $writer->save($output);

        return $output;
    }

    /**
     * @throws \Exception
     */
    public function setFile(string|UploadedFile $file): UploadedFile
    {
        $fileFromRequest = true;
        if (empty($file))
        {
            throw new \Exception('File Not Found');
        }

        if (is_string($file))
        {
            $fileFromRequest = false;
            if ( ! file_exists($file))
            {
                throw new \Exception('File Not Found');
            }
            $fileName = pathinfo($file, PATHINFO_BASENAME);
            $file     = new UploadedFile($file, $fileName);
        }

        if ( ! ($file instanceof UploadedFile))
        {
            throw new \Exception('File Not Found');
        }

        if ($fileFromRequest && ! $file->isValid())
        {
            throw new \Exception('file upload not success');
        }

        return $file;
    }

    public function setOutput($file): string
    {
        $fileName = Str::of(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))->append('_')->append('translated')->append('.')->append($file->getClientOriginalExtension())->snake();

        return 'translated/'.$fileName;
    }

    public function setTransTarget($target): static
    {
        $this->transTarget = $target;
        return $this;
    }

    public function getTransTarget(): string
    {
        return $this->transTarget ?: config('translate-spreadsheet.target');
    }

    public function setShouldRemoveSheet($action): static
    {
        $this->shouldRemoveSheet = $action;
        return $this;
    }

    public function getShouldRemoveSheet(): bool
    {
        return $this->shouldRemoveSheet ?: config('translate-spreadsheet.remove_sheet');
    }

    public function setTransSource($target): static
    {
        $this->transTarget = $target;
        return $this;
    }

    public function getTransSource(): string
    {
        return $this->transSource ?: config('translate-spreadsheet.source');
    }

    public function removeOldSheetAfterTranslate($sheetList, &$spreadsheet, $type = 'index'): void
    {
        foreach ($sheetList as $sheetIndex)
        {
            if ($type == 'sheet_name')
            {
                $sheetIndex = $spreadsheet->getIndex($spreadsheet->getSheetByName($sheetIndex));
            }

            $spreadsheet->removeSheetByIndex($sheetIndex);
        }
    }
}
