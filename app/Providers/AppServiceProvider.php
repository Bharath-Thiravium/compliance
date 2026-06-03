<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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

        // Force root URL for subdirectory deployment
        URL::forceRootUrl(config('app.url'));
        
        // Force HTTPS if behind proxy
        if ($this->app->request->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            URL::forceScheme('https');
        }

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
