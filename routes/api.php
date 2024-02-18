<?php

use App\Http\Controllers\AclController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

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

// V1 ROUTES
Route::group(['prefix' => 'v1'], function () {
    // PUBLIC ENDPOINTS
    Route::post('auth/login', [AuthController::class, 'login'])->name('login');

    // AUTHENTICATION REQUIRED ENDPOINTS
    Route::middleware(['auth:sanctum'])->group(function () {
        // AUTH ROUTES
        Route::group(['prefix' => 'auth'], function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('user', [AuthController::class, 'user']);
        });

        // USER ROUTES
        Route::apiResource('users', UserController::class);

        // AUDIT ROUTES
        Route::get('audits', [AuditController::class, 'getAllAudits']);

        // TOKENS ROUTES
        Route::get('tokens', [TokenController::class, 'getAllTokens']);
        Route::delete('tokens/{tokenId}', [TokenController::class, 'revoke']);

        // ACL ROUTES
        Route::group(['prefix' => 'acl'], function () {
            Route::get('roles', [AclController::class, 'getAllRoles']);
            Route::post('roles', [AclController::class, 'createRole']);
            Route::put('roles/{roleId}', [AclController::class, 'updateRole']);
            Route::post('roles/assign/{userId}', [AclController::class, 'assignRolesToUser']);
            Route::delete('roles/{roleId}', [AclController::class, 'deleteRole']);
            Route::get('permissions', [AclController::class, 'getAllPermissions']);
        });
    });
});
