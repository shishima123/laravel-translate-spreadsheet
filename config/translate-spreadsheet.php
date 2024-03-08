<?php
use Shishima\TranslateSpreadsheet\Enumerations\ClonePosition;
use Shishima\TranslateSpreadsheet\Enumerations\TranslateEngine;

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
    | The length of the sheet name is 31. If the length exceeds the limit, it will be cut off.
    |
    */
    'translate_sheet_name' => false,

    /*
    |--------------------------------------------------------------------------
    | Translate Engine
    |--------------------------------------------------------------------------
    | There are 2 engines are supported
    | TranslateEngine::Gemini;
    | TranslateEngine::Google;
    |
    */
    'translate_engine' => TranslateEngine::Gemini,

    /*
    |--------------------------------------------------------------------------
    | Translate Engine
    |--------------------------------------------------------------------------
    | Translate abbreviated language from Google Translate into its full language name.
    | Utilized for generating prompts in AI models.
    |
    */
    'mapping_abbreviation_language' => [
        'en' => 'english',
        'vi' => 'vietnamese',
    ],

    /*
    |--------------------------------------------------------------------------
    | Translate Engine
    |--------------------------------------------------------------------------
    | Log the translated text to a log file.
    |
    */
    'debug' => false,

    /*
    |--------------------------------------------------------------------------
    | Config for Gemini AI
    |--------------------------------------------------------------------------
    |
    */
    'gemini' => [
        //The wrap character definition will be removed from gemini's answer
        // Ex: "hello" -> hello
        'unwrap_character' => ['"', "'", "’", "‘"],

        // Rate limit for each model
        // https://docs.gemini.com/rest-api/#rate-limits
        'rate_limit' => 120
    ]
];
