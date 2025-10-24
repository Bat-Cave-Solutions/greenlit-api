<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/health', [HealthController::class, 'index']);

// Sanctum CSRF cookie route is provided by Sanctum when installed.
// Add your API routes below (projects, emissions, etc).
