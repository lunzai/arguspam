<?php

use App\Http\Controllers\AssetAccessGrantController;
use App\Http\Controllers\AssetAccountController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\OrgController;
use App\Http\Controllers\OrgUserController;
// use App\Http\Controllers\OrgUserGroupController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SessionAuditController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SettingGroupController;
use App\Http\Controllers\UserAccessRestrictionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGroupController;
use App\Http\Controllers\UserGroupUserController;
use App\Http\Controllers\UserRoleController;
use Illuminate\Support\Facades\Route;

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

    // SettingController routes
    Route::get('/settings', [SettingController::class, 'index']);
    Route::put('/settings', [SettingController::class, 'update']);

    // SettingGroupController routes
    Route::get('/settings/groups', [SettingGroupController::class, 'index']);
    Route::get('/settings/groups/{group}', [SettingGroupController::class, 'show']);

    // Audits
    Route::apiResource('audits', AuditController::class)
        ->only(['index', 'show']);

    Route::apiResource('session-audits', SessionAuditController::class)
        ->only(['index', 'show']);

    // Relationals
    Route::apiResource('users.roles', UserRoleController::class)
        ->only(['store']);
    Route::delete('users/{user}/roles', [UserRoleController::class, 'destroy'])
        ->name('users.roles.destroy');
    Route::apiResource('users.user-access-restrictions', UserAccessRestrictionController::class);
    // ->except(['store']);

    Route::apiResource('roles.permissions', RolePermissionController::class)
        ->only(['store']);
    Route::delete('roles/{role}/permissions', [RolePermissionController::class, 'destroy'])
        ->name('roles.permissions.destroy');

    Route::apiResource('user-groups.users', UserGroupUserController::class)
        ->only(['store']);
    Route::delete('user-groups/{user_group}/users', [UserGroupUserController::class, 'destroy'])
        ->name('user-groups.users.destroy');

    Route::apiResource('orgs.users', OrgUserController::class)
        ->only(['store']);
    Route::delete('orgs/{org}/users', [OrgUserController::class, 'destroy'])
        ->name('orgs.users.destroy');

    // Route::apiResource('orgs.user-groups', OrgUserGroupController::class)
    //     ->only(['store']);
    // Route::delete('orgs/{org}/user-groups', [OrgUserGroupController::class, 'destroy'])
    //     ->name('orgs.user-groups.destroy');

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
