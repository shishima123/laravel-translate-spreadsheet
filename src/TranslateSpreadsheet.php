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
            $clonedWorksheet->setTitle($sheetName.'_VN');
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
                        $textTranslated = GoogleTranslate::trans($value, 'vi', null);
                        $clonedWorksheet->setCellValue($cellCoordinate, $textTranslated);
                    }
                }
            }
            $spreadsheet->addSheet($clonedWorksheet, $index * 2 + 1);
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
        $fileName = Str::of(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                       ->append('_')
                       ->append('translated')
                       ->append('.')
                       ->append($file->getClientOriginalExtension())
                       ->snake();

        return 'translated/'.$fileName;
    }
}
