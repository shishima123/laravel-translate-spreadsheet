<?php

namespace Shishima\TranslateSpreadsheet\Tests;

use Illuminate\Support\Facades\File;
use Shishima\TranslateSpreadsheet\TranslateSpreadsheetServiceProvider;

class TestCase extends \Orchestra\Testbench\TestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            TranslateSpreadsheetServiceProvider::class
        ];
    }

    public function tearDown(): void
    {
        File::deleteDirectory(public_path(config('translate-spreadsheet.output_dir')));
        parent::tearDown();
    }
}
