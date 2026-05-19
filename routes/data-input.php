<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DataInputController;
use App\Http\Controllers\ComplianceDataUploadController;

Route::prefix('compliance')->middleware(['web', 'auth'])->group(function () {
    Route::post('/batch/{batch}/save-manual-data', [DataInputController::class, 'saveManualData'])->name('data.save-manual');
    Route::post('/form/upload/{batch}/{form}', [DataInputController::class, 'uploadPdfForm'])->name('data.upload-pdf');
    Route::post('/batch/{batch}/upload-csv', [DataInputController::class, 'uploadCsvData'])->name('data.upload-csv');

    // Multi-CSV upload
    Route::get('/data/upload',  [ComplianceDataUploadController::class, 'showForm'])->name('data.upload-multi.form');
    Route::post('/data/upload', [ComplianceDataUploadController::class, 'upload'])->name('data.upload-multi');

    // Sample CSV template downloads
    Route::get('/csv-template/employees',  fn() => app(ComplianceDataUploadController::class)->downloadTemplate('employees'))->name('csv.template.employees');
    Route::get('/csv-template/payroll',    fn() => app(ComplianceDataUploadController::class)->downloadTemplate('payroll'))->name('csv.template.payroll');
    Route::get('/csv-template/attendance', fn() => app(ComplianceDataUploadController::class)->downloadTemplate('attendance'))->name('csv.template.attendance');
});
