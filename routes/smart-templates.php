<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Compliance\SmartSupplementaryTemplateController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/compliance/templates/smart/{type}/download', [SmartSupplementaryTemplateController::class, 'download'])
        ->name('compliance.templates.smart.download');
});
