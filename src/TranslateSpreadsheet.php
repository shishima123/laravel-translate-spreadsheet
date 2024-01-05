<?php

namespace Shishima\TranslateSpreadsheet;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWrite;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Stichoza\GoogleTranslate\Exceptions\LargeTextException;
use Stichoza\GoogleTranslate\Exceptions\RateLimitException;
use Stichoza\GoogleTranslate\Exceptions\TranslationRequestException;
use Stichoza\GoogleTranslate\GoogleTranslate;
use Shishima\TranslateSpreadsheet\Enumerations\ClonePosition;
use Illuminate\Support\Facades\File;

class TranslateSpreadsheet
{
    private array|null $transTarget = null;

    private string|null $transSource = null;

    private bool|null $shouldRemoveSheet;

    private string|null $outputDir;

    private ClonePosition|null $clonePosition;

    private bool|null $isHighlightSheet;

    private bool|null $isTranslateSheetName;

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
            $currentSheetInstance = $spreadsheet->getSheetByName($sheetName);
            $currentSheetInstance = $this->handleTabColor($currentSheetInstance, $index);

            foreach (array_reverse($this->getTransTarget()) as $transTarget)
            {
                $clonedWorksheet = $this->translateSheet($currentSheetInstance, $transTarget);
                $spreadsheet->addSheet($clonedWorksheet, $this->getSheetCloneIndex($index));
            }
        }

        $this->handleRemoveOldSheetAfterTranslate(sheetNames: $sheetNames, spreadsheet: $spreadsheet);

        $this->makeDirectory();

        $output = $this->setOutput($file);
        $writer = new XlsxWrite($spreadsheet);
        $writer->save($output);

        return $output;
    }

    public function translateSheet($currentSheetInstance, string $transTarget)
    {
        $clonedWorksheet = clone $currentSheetInstance;
        $clonedWorksheet->setTitle($this->getTitle($currentSheetInstance->getTitle(), $transTarget));
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
                    $textTranslated = GoogleTranslate::trans(string: $value, target: $transTarget, source: $this->getTransSource());
                    $clonedWorksheet->setCellValue($cellCoordinate, $textTranslated);
                }
            }
        }
        return $clonedWorksheet;
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

    public function setOutput(UploadedFile $file): string
    {
        $fileName = Str::of(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
                       ->append('_')
                       ->append($this->getFileNameSuffix())
                       ->append('.')
                       ->append($file->getClientOriginalExtension());
        return public_path($this->getOutputDir().$fileName);
    }

    public function makeDirectory(): void
    {
        $path = public_path($this->getOutputDir());

        if ( ! File::isDirectory($path))
        {
            File::makeDirectory($path, 0777, true, true);
        }
    }

    /**
     * @throws \Exception
     */
    public function setTransTarget(string|array $target): static
    {
        if (empty($target))
        {
            throw new \Exception('Target is empty');
        }

        $this->transTarget = Arr::wrap($target);
        return $this;
    }

    public function getTransTarget(): array
    {
        return $this->transTarget ?? config('translate-spreadsheet.target');
    }

    public function setTransSource(string|null $source): static
    {
        $this->transSource = $source;
        return $this;
    }

    public function getTransSource(): string|null
    {
        return $this->transSource ?? config('translate-spreadsheet.source');
    }

    public function setOutputDir(string $outputDir): static
    {
        $this->outputDir = $outputDir;
        return $this;
    }

    public function getOutputDir(): string|null
    {
        return $this->outputDir ?? config('translate-spreadsheet.output_dir');
    }

    public function setShouldRemoveSheet(bool $action): static
    {
        $this->shouldRemoveSheet = $action;
        return $this;
    }

    public function shouldRemoveSheetAfterTranslate(): bool
    {
        return $this->shouldRemoveSheet ?? config('translate-spreadsheet.remove_sheet');
    }

    /**
     * @throws Exception
     */
    public function handleRemoveOldSheetAfterTranslate(array $sheetNames, Spreadsheet &$spreadsheet): void
    {
        if ($this->shouldRemoveSheetAfterTranslate())
        {
            foreach ($sheetNames as $sheetName)
            {
                $sheetIndex = $spreadsheet->getIndex($spreadsheet->getSheetByName($sheetName));

                $spreadsheet->removeSheetByIndex($sheetIndex);
            }
        }
    }

    public function setCloneSheetPosition(ClonePosition $clonePosition): static
    {
        $this->clonePosition = $clonePosition;
        return $this;
    }

    public function getCloneSheetPosition(): ClonePosition
    {
        return $this->clonePosition ?? config('translate-spreadsheet.clone_sheet_position');
    }

    public function getSheetCloneIndex(int $index): int|null
    {
        return match ($this->getCloneSheetPosition())
        {
            ClonePosition::PrependCurrentSheet => $index * (count($this->getTransTarget()) + 1),
            ClonePosition::AppendLastSheet => null,
            ClonePosition::PrependFirstSheet => $index,
            default => $index * (count($this->getTransTarget()) + 1) + 1,
        };
    }

    public function getTitle(string $sheetName, string $transTarget): string
    {
        if ($this->getIsTranslateSheetName())
        {
            $sheetName = GoogleTranslate::trans(string: $sheetName, target: $transTarget);
        }
        return $sheetName.'_'.Str::upper($transTarget);
    }

    public function getFileNameSuffix(): string
    {
        if (empty(config('translate-spreadsheet.suffix')))
        {
            return implode('_', $this->getTransTarget());
        }

        return implode('_', config('translate-spreadsheet.suffix'));
    }

    public function getIsHighlightSheet(): bool
    {
        return $this->isHighlightSheet ?? config('translate-spreadsheet.highlight_sheet');
    }

    public function highlightSheet(bool $action = true): static
    {
        $this->isHighlightSheet = $action;
        return $this;
    }

    /**
     * @throws \Exception
     */
    public function handleTabColor(Worksheet $currentSheetInstance, int $index): Worksheet
    {
        if ($this->getIsHighlightSheet())
        {
            $color = $this->getTabColor($index);
            $currentSheetInstance->getTabColor()->setRGB($color);
        }
        return $currentSheetInstance;
    }

    /**
     * @throws \Exception
     */
    public function getTabColor(int $index): string
    {
        $palettes = config('translate-spreadsheet.highlight_palette_colors');

        if (empty($palettes))
        {
            throw new \Exception('No color found!');
        }

        $newIndex = $index % count($palettes);

        return $palettes[$newIndex];
    }

    public function getIsTranslateSheetName(): bool
    {
        return $this->isTranslateSheetName ?? config('translate-spreadsheet.translate_sheet_name');
    }

    public function translateSheetName(bool $action = true): static
    {
        $this->isTranslateSheetName = $action;
        return $this;
    }
}
