<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataInputController;
use App\Http\Controllers\ComplianceDataUploadController;
use App\Http\Controllers\CsvTemplateController;

Route::prefix('compliance')->middleware(['web', 'auth'])->group(function () {
    Route::post('/batch/{batch}/save-manual-data', [DataInputController::class, 'saveManualData'])->name('data.save-manual');
    Route::post('/form/upload/{batch}/{form}', [DataInputController::class, 'uploadPdfForm'])->name('data.upload-pdf');
    Route::post('/batch/{batch}/upload-csv', [DataInputController::class, 'uploadCsvData'])->name('data.upload-csv');

    // Multi-CSV upload
    Route::get('/data/upload',  [ComplianceDataUploadController::class, 'showForm'])->name('data.upload-multi.form');
    Route::post('/data/upload', [ComplianceDataUploadController::class, 'upload'])->name('data.upload-multi');

    // Supplementary dataset upload (bonus/fines/advances/deductions/incidents/hazard_register/contractors)
    Route::post('/data/upload-supplementary', [ComplianceDataUploadController::class, 'uploadSupplementary'])->name('data.upload-supplementary');

    // CSV template metadata index
    Route::get('/csv-templates', [CsvTemplateController::class, 'index'])->name('csv.templates.index');

    // Unified CSV template download
    // GET /compliance/csv-template/{type}
    // Supported types: employees, attendance, payroll, bonus, fines, advances,
    //                  deductions, incidents, hazard_register, contractors
    Route::get('/csv-template/{type}', [CsvTemplateController::class, 'download'])->name('csv.template');
});
