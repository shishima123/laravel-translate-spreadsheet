<?php

namespace Shishima\TranslateSpreadsheet;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx as XlsxReader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWrite;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Shishima\TranslateSpreadsheet\Strategy\Translate\TranslateContext;
use Shishima\TranslateSpreadsheet\Enumerations\ClonePosition;
use Shishima\TranslateSpreadsheet\Enumerations\TranslateEngine;
use Illuminate\Support\Facades\File;

class TranslateSpreadsheet
{
    const MAX_SHEET_NAME_LENGTH = 31;
    private array|null $transTarget = null;

    private string|null $transSource = null;

    private bool|null $shouldRemoveSheet;

    private string|null $outputDir;

    private ClonePosition|null $clonePosition;

    private bool|null $isHighlightSheet;

    private bool|null $isTranslateSheetName;

    private TranslateEngine|null $translateEngine;

    private bool|null $isDebug;

    /**
     * @throws Exception
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
        $translateContext = new TranslateContext($this->getTranslateEngine());
        return $translateContext->doTranslate($currentSheetInstance, $this, $transTarget);
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
        $fileName = Str::of(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))->append('_')->append($this->getFileNameSuffix())->append('.')->append($file->getClientOriginalExtension());
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

    public function getTitle(string $sheetName, string $transTarget, $translateEngine): string
    {
        if ($this->getIsTranslateSheetName())
        {
            $sheetName = $translateEngine->translateText($sheetName, $transTarget);
        }
        $sheetName = Str::substr($sheetName, 0, self::MAX_SHEET_NAME_LENGTH - strlen($transTarget) - 1);
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

    public function setTranslateEngine($translateEngine): static
    {
        $this->translateEngine = $translateEngine;
        return $this;
    }

    public function getTranslateEngine(): TranslateEngine
    {
        return $this->translateEngine ?? config('translate-spreadsheet.translate_engine');
    }

    public function isNonEmptyStr($value): bool
    {
        return Str::of($value)->isNotEmpty();
    }

    public function isNonNumeric($value): bool
    {
        return ! is_numeric($value);
    }

    public function enableDebug(bool $action = true): static
    {
        $this->isDebug = $action;
        return $this;
    }

    public function isEnableDebug(): bool
    {
        return $this->isDebug ?? config('translate-spreadsheet.debug');
    }
}
