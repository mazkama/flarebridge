<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SubdomainController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\SettingController;
use App\Http\Controllers\Api\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->post('/logout', [AuthController::class, 'logout']);

Route::get('/onboarding-check', [SettingController::class, 'checkOnboarding']);
Route::post('/setup', [SettingController::class, 'setup']);
Route::post('/v1/system/validate-step', [SettingController::class, 'validateStep']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Settings Management
    Route::get('/settings', [SettingController::class, 'index']);
    Route::post('/settings', [SettingController::class, 'store']);

    // Domain Management
    Route::apiResource('domains', DomainController::class);

    // Subdomain Management
    Route::apiResource('subdomains', SubdomainController::class)->only(['index', 'store', 'destroy']);

    // System Operations
    Route::post('/system/reset', [SettingController::class, 'resetSystem']);
    Route::get('/system/token', [SettingController::class, 'viewToken']);
    Route::post('/system/token/renew', [SettingController::class, 'renewToken']);
});
