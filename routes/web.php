<?php

use App\Livewire\LandingPage;
use Illuminate\Support\Facades\Route;

// Landing page (now using Livewire)
Route::get('/', LandingPage::class)->name('landing');
