<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\ComplianceFormController;

Route::prefix('api/compliance/forms')->middleware(['api'])->group(function () {
    Route::get('/form10', [ComplianceFormController::class, 'form10']);
    Route::get('/form12', [ComplianceFormController::class, 'form12']);
    Route::get('/form17', [ComplianceFormController::class, 'form17']);
    Route::get('/form25', [ComplianceFormController::class, 'form25']);
    Route::get('/formB', [ComplianceFormController::class, 'formB']);
    Route::get('/form26', [ComplianceFormController::class, 'form26']);
    Route::get('/form26A', [ComplianceFormController::class, 'form26A']);
    Route::get('/hazard', [ComplianceFormController::class, 'hazard']);
    
    // CLRA Forms
    Route::get('/formXII', [ComplianceFormController::class, 'formXII']);
    Route::get('/formXIII', [ComplianceFormController::class, 'formXIII']);
    Route::get('/formXIV', [ComplianceFormController::class, 'formXIV']);
    Route::get('/formXVI', [ComplianceFormController::class, 'formXVI']);
    Route::get('/formXVII', [ComplianceFormController::class, 'formXVII']);
    Route::get('/formXVIII', [ComplianceFormController::class, 'formXVIII']);
    Route::get('/formXIX', [ComplianceFormController::class, 'formXIX']);
    Route::get('/formXX', [ComplianceFormController::class, 'formXX']);
    Route::get('/formXXI', [ComplianceFormController::class, 'formXXI']);
    Route::get('/formXXII', [ComplianceFormController::class, 'formXXII']);
    Route::get('/formXXIII', [ComplianceFormController::class, 'formXXIII']);
    
    // Labour Welfare Forms
    Route::get('/formA', [ComplianceFormController::class, 'formA']);
    Route::get('/formC', [ComplianceFormController::class, 'formC']);
    Route::get('/formD', [ComplianceFormController::class, 'formD']);
    Route::get('/formDER', [ComplianceFormController::class, 'formDER']);
    
    // Factories Act Forms
    Route::get('/form2', [ComplianceFormController::class, 'form2']);
    Route::get('/form8', [ComplianceFormController::class, 'form8']);
    Route::get('/form11', [ComplianceFormController::class, 'form11']);
    Route::get('/form18', [ComplianceFormController::class, 'form18']);
    
    // Social Security Forms
    Route::get('/esiForm12', [ComplianceFormController::class, 'esiForm12']);
    Route::get('/epfInspection', [ComplianceFormController::class, 'epfInspection']);
    
    // Shops & Establishment Forms
    Route::get('/shopsFormC', [ComplianceFormController::class, 'shopsFormC']);
    Route::get('/shopsUnpaid', [ComplianceFormController::class, 'shopsUnpaid']);
    Route::get('/shopsForm12', [ComplianceFormController::class, 'shopsForm12']);
    Route::get('/shopsForm13', [ComplianceFormController::class, 'shopsForm13']);
    Route::get('/shopsFines', [ComplianceFormController::class, 'shopsFines']);
    Route::get('/shopsFormVI', [ComplianceFormController::class, 'shopsFormVI']);
});