<?php

use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\WelcomeController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Welcome page
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Features pages
Route::get('/features', [FeaturesController::class, 'index'])->name('features.index');
Route::get('/features/{id}', [FeaturesController::class, 'show'])->name('features.show');
