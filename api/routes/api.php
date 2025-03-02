<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrgController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/', function () {
    return 'Hello World';
});

//Route::apiResource('organizations', OrgController::class);


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
