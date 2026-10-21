<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TatReportController;
use App\Http\Controllers\DailyResultController;
use App\Http\Controllers\ResultEntryController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');


Route::prefix('tat-report')->group(function () {
    Route::get('/',        [TatReportController::class, 'index']);
    Route::get('/filters', [TatReportController::class, 'filters']);
});

Route::get('/daily-result-summary', [DailyResultController::class, 'dailyResultSummary']);

Route::prefix('results')->group(function () {
    Route::get('/filters',              [ResultEntryController::class, 'filters']);
    Route::get('/labno-list',           [ResultEntryController::class, 'labnoList']);
    Route::get('/details/{labno}',      [ResultEntryController::class, 'details']);
    Route::get('/history/{labno}',      [ResultEntryController::class, 'history']);
    Route::post('/save',                [ResultEntryController::class, 'save']);
    Route::post('/release',             [ResultEntryController::class, 'release']);
});
