<?php

use App\Http\Controllers\AclController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group(['prefix' => 'v1'], function () {
    // PUBLIC ENDPOINTS
    Route::post('auth/login', [AuthController::class, 'login'])->name('login');

    // AUTHENTICATION REQUIRED ENDPOINTS
    Route::middleware('auth:sanctum')->group(function () {
        // AUTH ROUTES
        Route::group(['prefix' => 'auth'], function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('user', [AuthController::class, 'user']);
        });

        // USER ROUTES
        Route::apiResource('users', UserController::class);

        // ACL ROUTES
        Route::group(['prefix' => 'acl', 'middleware' => 'role:Super admin'], function () {
            Route::get('roles', [AclController::class, 'getAllRoles']);
            Route::post('roles', [AclController::class, 'createRole']);
            Route::put('roles/{roleId}', [AclController::class, 'updateRole']);
            Route::post('roles/assign/{userId}', [AclController::class, 'assignRolesToUser']);
            Route::delete('roles/{roleId}', [AclController::class, 'deleteRole']);
            Route::get('permissions', [AclController::class, 'getAllPermissions']);
        });
    });
});
