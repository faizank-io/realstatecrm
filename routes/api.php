<?php

use App\Http\Controllers\Api\Admin\AdminAgencyController;
use App\Http\Controllers\Api\Admin\AdminSettingsController;
use App\Http\Controllers\Api\Admin\AdminSubscriptionPlanController;
use App\Http\Controllers\Api\Agency\AgencyRoleController;
use App\Http\Controllers\Api\Agency\AgencyTeamController;
use App\Http\Controllers\Api\Agency\LeadController;
use App\Http\Controllers\Api\Agency\PropertyController;
use App\Http\Controllers\Api\Auth\AuthController;
use Illuminate\Support\Facades\Route;

// Public Auth Endpoints
Route::post('/auth/register-agency', [AuthController::class, 'registerAgency']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/super-admin-login', [AuthController::class, 'superAdminLogin']);
Route::get('/auth/me', [AuthController::class, 'me']);
Route::post('/auth/logout', [AuthController::class, 'logout']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    // Super Admin Routes
    Route::prefix('admin')->group(function () {
        Route::apiResource('plans', AdminSubscriptionPlanController::class);
        Route::get('agencies', [AdminAgencyController::class, 'index']);
        Route::put('agencies/{agency}/status', [AdminAgencyController::class, 'updateStatus']);
        Route::put('agencies/{agency}/extend-trial', [AdminAgencyController::class, 'extendTrial']);
        Route::post('agencies/{agency}/assign-plan', [AdminAgencyController::class, 'assignPlan']);
        Route::get('settings', [AdminSettingsController::class, 'index']);
        Route::put('settings', [AdminSettingsController::class, 'update']);
    });

    // Agency Workspace Routes
    Route::prefix('agency')->group(function () {
        Route::get('team', [AgencyTeamController::class, 'index']);
        Route::post('team', [AgencyTeamController::class, 'store']);
        Route::put('team/{user}/status', [AgencyTeamController::class, 'updateStatus']);

        Route::apiResource('roles', AgencyRoleController::class);
        Route::apiResource('properties', PropertyController::class);
        Route::apiResource('leads', LeadController::class);
    });
});
