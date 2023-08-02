<?php

namespace Shishima\TranslateSpreadsheet\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Shishima\TranslateSpreadsheet\Skeleton\SkeletonClass
 */
class TranslateSpreadsheet extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'translate-spreadsheet';
    }
}
