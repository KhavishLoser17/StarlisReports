<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});
Route::get('/tat-report', function () {
    return view('tat-report');
})->name('tat-report');

Route::get('/daily-result-summary', function () {
    return view('daily-result');
})->name('daily-result');

Route::get('/result-entry', function () {
    return view('result-entry');
})->name('result-entry');