<?php

use App\Http\Controllers\AccessRestrictionController;
use App\Http\Controllers\AccessRestrictionUserController;
use App\Http\Controllers\AccessRestrictionUserGroupController;
use App\Http\Controllers\AssetAccessGrantController;
use App\Http\Controllers\AssetAccountController;
use App\Http\Controllers\AssetConnectionController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\AuditController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrgController;
use App\Http\Controllers\OrgUserController;
use App\Http\Controllers\OrgUserGroupController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\SessionAuditController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SettingGroupController;
use App\Http\Controllers\TimezoneController;
use App\Http\Controllers\TwoFactorAuthenticationController;
use App\Http\Controllers\UserAccessRestrictionController;
use App\Http\Controllers\UserAssetController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGroupController;
use App\Http\Controllers\UserGroupUserController;
use App\Http\Controllers\UserOrgController;
use App\Http\Controllers\UserRoleController;
use App\Http\Middleware\EnsureOrganizationIdIsValid;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/2fa', [AuthController::class, 'verifyTwoFactor']);
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');
});

Route::prefix('utils')->group(function () {
    Route::get('/timezones', [TimezoneController::class, 'index']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::get('/users/me', [AuthController::class, 'me']); // Alias for /auth/me
    Route::get('/users/me/orgs', [UserOrgController::class, 'index']);
    Route::get('/users/me/orgs/{org}', [UserOrgController::class, 'show']);
    Route::put('/users/me/change-password', [PasswordController::class, 'update']);
    Route::get('/users/me/assets', [UserAssetController::class, 'index']);

    Route::middleware(EnsureOrganizationIdIsValid::class)->group(function () {
        Route::apiResources([
            'assets' => AssetController::class,
            'requests' => RequestController::class,
            'sessions' => SessionController::class,
            'user-groups' => UserGroupController::class,
        ]);

        Route::apiResource('user-groups.users', UserGroupUserController::class)
            ->only(['store']);
        Route::delete('user-groups/{user_group}/users', [UserGroupUserController::class, 'destroy'])
            ->name('user-groups.users.destroy');

        Route::post('/assets/{asset}/access-grant', [AssetAccessGrantController::class, 'store'])
            ->name('assets.access-grants.store');
        Route::delete('/assets/{asset}/access-grant', [AssetAccessGrantController::class, 'destroy'])
            ->name('assets.access-grants.destroy');
        Route::get('/assets/{asset}/connection', [AssetConnectionController::class, 'show'])
            ->name('assets.connection.show');
        Route::put('/assets/{asset}/credential', [AssetAccountController::class, 'update'])
            ->name('assets.credential.update');

        // Route::get('assets/{asset}/grants', [AssetAccessGrantController::class, 'index'])
        //     ->name('assets.access-grants.index');
        // Route::get('assets/{asset}/grants/{asset_access_grant}', [AssetAccessGrantController::class, 'show'])
        //     ->name('assets.access-grants.show');
        // Route::post('assets/{asset}/grants', [AssetAccessGrantController::class, 'store'])
        //     ->name('assets.access-grants.store');
        // Route::delete('assets/{asset}/grants/{asset_access_grant}', [AssetAccessGrantController::class, 'destroy'])
        //     ->name('assets.access-grants.destroy');
        Route::get('dashboard', [DashboardController::class, 'index'])
            ->name('dashboard.index');
    });

    Route::apiResources([
        'orgs' => OrgController::class,
        'users' => UserController::class,
        'roles' => RoleController::class,
        'permissions' => PermissionController::class,
        'access-restrictions' => AccessRestrictionController::class,
    ]);

    Route::get('/settings', [SettingController::class, 'index']);
    Route::put('/settings', [SettingController::class, 'update']);
    Route::get('/settings/groups', [SettingGroupController::class, 'index']);
    Route::get('/settings/groups/{group}', [SettingGroupController::class, 'show']);
    Route::apiResource('audits', AuditController::class)
        ->only(['index', 'show']);
    Route::apiResource('session-audits', SessionAuditController::class)
        ->only(['index', 'show']);
    Route::get('/users/{user}/2fa', [TwoFactorAuthenticationController::class, 'show'])
        ->name('users.two-factor-authentication.show');
    Route::post('/users/{user}/2fa', [TwoFactorAuthenticationController::class, 'store'])
        ->name('users.two-factor-authentication.store');
    Route::put('/users/{user}/2fa', [TwoFactorAuthenticationController::class, 'update'])
        ->name('users.two-factor-authentication.update');
    Route::delete('/users/{user}/2fa', [TwoFactorAuthenticationController::class, 'destroy'])
        ->name('users.two-factor-authentication.destroy');
    Route::post('/users/{user}/reset-password', [PasswordController::class, 'store'])
        ->name('users.reset-password.store');
    Route::apiResource('users.roles', UserRoleController::class)
        ->only(['store']);
    Route::delete('users/{user}/roles', [UserRoleController::class, 'destroy'])
        ->name('users.roles.destroy');
    Route::apiResource('users.user-access-restrictions', UserAccessRestrictionController::class);
    Route::get('roles/{role}/permissions', [RolePermissionController::class, 'index'])
        ->name('roles.permissions.index');
    Route::put('roles/{role}/permissions', [RolePermissionController::class, 'update'])
        ->name('roles.permissions.update');
    Route::delete('roles/{role}/permissions', [RolePermissionController::class, 'destroy'])
        ->name('roles.permissions.destroy');
    Route::apiResource('orgs.users', OrgUserController::class)
        ->only(['store', 'index']);
    Route::apiResource('orgs.user-groups', OrgUserGroupController::class)
        ->only(['index']);
    Route::delete('orgs/{org}/users', [OrgUserController::class, 'destroy'])
        ->name('orgs.users.destroy');
    Route::apiResource('access-restrictions.users', AccessRestrictionUserController::class)
        ->only(['store', 'index']);
    Route::delete('access-restrictions/{access_restriction}/users', [AccessRestrictionUserController::class, 'destroy'])
        ->name('access-restrictions.users.destroy');
    Route::apiResource('access-restrictions.user-groups', AccessRestrictionUserGroupController::class)
        ->only(['store', 'index']);
    Route::delete('access-restrictions/{access_restriction}/user-groups', [AccessRestrictionUserGroupController::class, 'destroy'])
        ->name('access-restrictions.user-groups.destroy');

});
