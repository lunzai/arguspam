<?php

use App\Http\Controllers\AssetAccessGrantController;
use App\Http\Controllers\AssetAccountController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrgController;
use App\Http\Controllers\OrgUserController;
use App\Http\Controllers\OrgUserGroupController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SessionAuditController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGroupController;
use App\Http\Controllers\UserGroupUserController;
use Illuminate\Support\Facades\Route;

// use App\Http\Middleware\EnsureOrganizationIdIsValid;

Route::get('/', function () {
    return response()->json(['message' => 'Hello World']);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResource('orgs.users', OrgUserController::class)
        ->only(['index']);
    Route::apiResource('user-groups.users', UserGroupUserController::class)
        ->only(['index']);
});

// Route::middleware(['auth:sanctum', EnsureOrganizationIdIsValid::class])->group(function () {
Route::middleware('auth:sanctum')->group(function () {

    Route::apiResources([
        'assets' => AssetController::class,
        'orgs' => OrgController::class,
        'requests' => RequestController::class,
        'sessions' => SessionController::class,
        'users' => UserController::class,
        'user-groups' => UserGroupController::class,
        'roles' => RoleController::class,
        'permissions' => PermissionController::class,
    ]);

    // Audits
    Route::apiResource('audits', AuditController::class)
        ->only(['index', 'show']);

    Route::apiResource('session-audits', SessionAuditController::class)
        ->only(['index', 'show']);

    // Relationals
    Route::apiResource('user-groups.users', UserGroupUserController::class)
        ->only(['store', 'destroy']);

    Route::apiResource('orgs.users', OrgUserController::class)
        ->only(['store', 'destroy']);

    Route::apiResource('orgs.user-groups', OrgUserGroupController::class)
        ->only(['index', 'store', 'destroy']);

    Route::apiResource('assets.accounts', AssetAccountController::class)
        ->only(['index', 'store', 'destroy']);

    Route::apiResource('assets.access-grants', AssetAccessGrantController::class)
        ->only(['index', 'store', 'destroy']);

    // Relational with update
    Route::apiResource('assets.access-grants', AssetAccessGrantController::class)
        ->only(['index', 'store', 'update', 'destroy']);
});
