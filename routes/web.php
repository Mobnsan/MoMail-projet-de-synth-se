<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\CampaignController;
use App\Http\Controllers\SettingController;

// Ping route
Route::get('/api/ping', function () {
    return response()->json(['status' => 'ok']);
});

Route::prefix('api')->group(function () {
    // Auth Routes
    Route::post('/auth/signup', [AuthController::class, 'signup']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Protected Routes
    Route::middleware('auth')->group(function () {
        // Contacts
        Route::post('/contacts/upload', [ContactController::class, 'upload']);
        Route::get('/contacts', [ContactController::class, 'index']);
        Route::post('/contacts', [ContactController::class, 'store']);
        Route::put('/contacts/{id}', [ContactController::class, 'update']);
        Route::delete('/contacts/{id}', [ContactController::class, 'destroy']);

        // Templates
        Route::get('/templates', [TemplateController::class, 'index']);
        Route::post('/templates', [TemplateController::class, 'store']);
        Route::post('/templates/{id}/duplicate', [TemplateController::class, 'duplicate']);
        Route::delete('/templates/{id}', [TemplateController::class, 'destroy']);

        // Campaigns
        Route::get('/campaigns', [CampaignController::class, 'index']);
        Route::post('/campaigns', [CampaignController::class, 'store']);

        // Settings
        Route::get('/settings', [SettingController::class, 'index']);
        Route::get('/settings/providers', [SettingController::class, 'providers']);
        Route::post('/settings', [SettingController::class, 'store']);
        Route::post('/settings/auto-connect', [SettingController::class, 'autoConnect']);
    });
});

// Fallback route to serve React frontend (assuming React build is copied to public/)
Route::get('/{any}', function () {
    return file_get_contents(public_path('index.html'));
})->where('any', '^(?!api).*$');
