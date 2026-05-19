<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BatchProcessingController;

Route::prefix('compliance')->middleware(['web', 'auth'])->group(function () {
    Route::post('/batch/{batch}/process-next', [BatchProcessingController::class, 'processNextForm'])->name('batch.process.next');
    Route::get('/batch/{batch}/progress', [BatchProcessingController::class, 'getBatchProgress'])->name('batch.progress');
});
