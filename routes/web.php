<?php

use App\Http\Controllers\Auth\CustomAuthController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

// Landing page
Route::get('/', [LandingController::class, 'index'])->name('landing');

// Authentication routes
Route::post('/login', [CustomAuthController::class, 'login'])->name('custom.login');
Route::post('/logout', [CustomAuthController::class, 'logout'])->name('logout');
