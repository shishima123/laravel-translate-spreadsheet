<?php
use Shishima\TranslateSpreadsheet\Enumerations\ClonePosition;

return [
    /*
    |--------------------------------------------------------------------------
    | Google translate target
    |--------------------------------------------------------------------------
    |
    | Setting the language target of Google Translate
    |
    */
    'target' => 'en',

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
    'clone_sheet_position' => ClonePosition::AppendLastSheet,

    /*
    |--------------------------------------------------------------------------
    | Directory store files
    |--------------------------------------------------------------------------
    |
    | Configure the directory where the file will be saved after translating
    |
    */
    'output_dir' => 'translated/'
];