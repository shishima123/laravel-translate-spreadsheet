<?php

namespace Shishima\TranslateSpreadsheet\Tests;

use Shishima\TranslateSpreadsheet\Facades\TranslateSpreadsheet;

class TranslateSpreadsheetTest extends TestCase
{
    public function test_can_translate_spreadsheet_file()
    {
        $result = TranslateSpreadsheet::translate(__DIR__.'/test.xlsx');
        static::assertFileExists($result);
    }
}
