<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        config(['telescope.enabled' => false]);

        // Ensure required storage directories exist (safe on every boot)
        foreach ([
            storage_path('app/compliance_pdfs'),
            storage_path('app/compliance_inspection_packs'),
        ] as $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }
        }
    }
}
