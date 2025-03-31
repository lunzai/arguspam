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
use App\Http\Controllers\UserRoleController;
use App\Http\Controllers\RolePermissionController;

Route::get('/', function () {
    return response()->json(['message' => 'Hello World']);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');
});

// Route::middleware('auth:sanctum')->group(function () {
//     Route::apiResource('orgs.users', OrgUserController::class)
//         ->only(['index']);
//     Route::apiResource('user-groups.users', UserGroupUserController::class)
//         ->only(['index']);
// });

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
    Route::apiResource('users.roles', UserRoleController::class)
        ->only(['store', 'destroy']);

    Route::apiResource('roles.permissions', RolePermissionController::class)
        ->only(['store', 'destroy']);

    Route::apiResource('user-groups.users', UserGroupUserController::class)
        ->only(['store', 'destroy']);

    Route::apiResource('orgs.users', OrgUserController::class)
        ->only(['store', 'destroy']);

    Route::apiResource('orgs.user-groups', OrgUserGroupController::class)
        ->only(['store', 'destroy']);

    Route::post('assets/{asset}/accounts', [AssetAccountController::class, 'store'])
        ->name('assets.accounts.store');

    Route::delete('assets/{asset}/accounts/{account}', [AssetAccountController::class, 'destroy'])
        ->name('assets.accounts.destroy');

    Route::get('assets/{asset}/grants', [AssetAccessGrantController::class, 'index'])
        ->name('assets.access-grants.index');

    Route::get('assets/{asset}/grants/{asset_access_grant}', [AssetAccessGrantController::class, 'show'])
        ->name('assets.access-grants.show');

    Route::post('assets/{asset}/grants', [AssetAccessGrantController::class, 'store'])
        ->name('assets.access-grants.store');

    Route::delete('assets/{asset}/grants/{asset_access_grant}', [AssetAccessGrantController::class, 'destroy'])
        ->name('assets.access-grants.destroy');
});
