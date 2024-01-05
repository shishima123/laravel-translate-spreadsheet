<?php
use Shishima\TranslateSpreadsheet\Enumerations\ClonePosition;

return [
    /*
    |--------------------------------------------------------------------------
    | Google translate target
    |--------------------------------------------------------------------------
    |
    | Setting the language target of Google Translate
    | If you want to translate into multiple languages simultaneously, set multiple values in the array.
    |
    */
    'target' => ['en'],

    /*
    |--------------------------------------------------------------------------
    | Google translate source
    |--------------------------------------------------------------------------
    |
    | Setting the language source of Google Translate
    |
    */
    'source' => null,

    /*
    |--------------------------------------------------------------------------
    | Remove sheet after translate
    |--------------------------------------------------------------------------
    |
    | If set is true, the translated sheets will be removed
    |
    */
    'remove_sheet' => false,

    /*
    |--------------------------------------------------------------------------
    | Position of eew sheet after translate
    |--------------------------------------------------------------------------
    |
    | There are 4 options to config
    | ClonePosition::AppendCurrentSheet;
    | ClonePosition::PrependCurrentSheet;
    | ClonePosition::PrependFirstSheet;
    | ClonePosition::AppendLastSheet;
    |
    */
    'clone_sheet_position' => ClonePosition::AppendCurrentSheet,

    /*
    |--------------------------------------------------------------------------
    | Directory store files
    |--------------------------------------------------------------------------
    |
    | Configure the directory where the file will be saved after translating
    |
    */
    'output_dir' => 'translated/',

    /*
    |--------------------------------------------------------------------------
    | Directory store files
    |--------------------------------------------------------------------------
    |
    | Configure the suffix in the output file name
    | If set to null, the suffix will take the value of the target of the translation. E.g: _en
    |
    */
    'suffix' => null,

    /*
    |--------------------------------------------------------------------------
    | Highlight Sheet
    |--------------------------------------------------------------------------
    |
    | Color configuration for tabs
    | Translated sheets will have the same color as the original sheet.
    |
    */
    'highlight_sheet' => false,

    /*
    |--------------------------------------------------------------------------
    | Highlight Palette Colors
    |--------------------------------------------------------------------------
    |
    | Define the color scheme for the tab.
    |
    */
    'highlight_palette_colors' => [
        'FBF8CC',
        'FDE4CF',
        'FFCFD2',
        'F1C0E8',
        'CFBAF0',
        'A3C4F3',
        '90DBF4',
        '8EECF5',
        '98F5E1',
        'B9FBC0',
    ],

    /*
    |--------------------------------------------------------------------------
    | Translate Sheet Name
    |--------------------------------------------------------------------------
    |
    | The configuration is used for translating sheet names.
    |
    */
    'translate_sheet_name' => false,
];
