<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\SubdomainController;
use App\Http\Controllers\Api\DomainController;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->middleware('auth:sanctum')->group(function () {
    // Domain Management
    Route::apiResource('domains', DomainController::class);

    // Subdomain Management
    Route::apiResource('subdomains', SubdomainController::class)->only(['index', 'store', 'destroy']);
});
