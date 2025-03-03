<?php

use App\Http\Controllers\AssetController;
use App\Http\Controllers\OrgController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\SessionController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserGroupController;
use App\Http\Controllers\AuditController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SessionAuditController;

Route::get('/', function () {
    return response()->json(['message' => 'Hello World']);
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout'])
        ->middleware('auth:sanctum');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::apiResources([
        'assets' => AssetController::class,
        'organizations' => OrgController::class,
        'requests' => RequestController::class,
        'sessions' => SessionController::class,
        'users' => UserController::class,
        'user-groups' => UserGroupController::class,
        'audits' => AuditController::class,
        'session-audits' => SessionAuditController::class,
    ]);
});

/**
 * Org
 * - CRUD
 * - user/add
 * - user/remove
 * - user_group/add
 * - user_group/remove
 * User
 * - CRUD
 * - user_group/add
 * - user_group/remove
 * - asset_access_grant/add
 * - asset_access_grant/remove
 * UserGroup
 * - CRUD
 * - user/add
 * - user/remove
 * Asset
 * - CRUD
 * - asset_account/add
 * - asset_account/remove
 * AssetAccount
 * - CRUD
 */
// Org

// User
// UserGroup
// Asset
// AssetAccount
// AssetAccessGrant
// Request
// Session
// SessionAudit
// ActionAudit
// UserAccessRestriction
