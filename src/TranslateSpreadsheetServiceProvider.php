<?php

namespace Shishima\TranslateSpreadsheet;

use Illuminate\Support\ServiceProvider;

class TranslateSpreadsheetServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/translate-spreadsheet.php' => config_path('translate-spreadsheet.php'),
            ], 'translate-spreadsheet');
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/translate-spreadsheet.php', 'translate-spreadsheet');

        // Register the main class to use with the facade
        $this->app->singleton('translate-spreadsheet', function () {
            return new TranslateSpreadsheet;
        });
    }
}
