<?php

namespace App\Providers;

use App\Services\PdfToTextService;
use Illuminate\Support\ServiceProvider;

class PdfToTextServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(PdfToTextService::class, function ($app) {
            return new PdfToTextService();
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
