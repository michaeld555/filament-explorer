<?php

namespace Michaeld555\FilamentExplorer;

use Illuminate\Support\ServiceProvider;

class FilamentExplorerServiceProvider extends ServiceProvider
{

    public function register(): void
    {

        // Register generate command

        $this->commands([
           \Michaeld555\FilamentExplorer\Console\FilamentExplorerInstall::class,
        ]);

        // Register Config file

        $this->mergeConfigFrom(__DIR__.'/../config/filament-explorer.php', 'filament-explorer');

        // Publish Config

        $this->publishes([
           __DIR__.'/../config/filament-explorer.php' => config_path('filament-explorer.php'),
        ], 'filament-explorer-config');

        // Register views

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'filament-explorer');

        // Publish Views

        $this->publishes([
           __DIR__.'/../resources/views' => resource_path('views/vendor/filament-explorer'),
        ], 'filament-explorer-views');

        // Register Langs

        $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'filament-explorer');

        // Publish Lang

        $this->publishes([
           __DIR__.'/../resources/lang' => base_path('lang/vendor/filament-explorer'),
        ], 'filament-explorer-lang');

        // Register Routes

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');

    }

    public function boot(): void
    {
        //
    }

}
