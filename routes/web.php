<?php

use Illuminate\Support\Facades\Route;

// Render Vue SPA for all web routes
Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '.*');
