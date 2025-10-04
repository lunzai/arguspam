<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Enums\RequestStatus;
use App\Enums\SessionStatus;
use App\Enums\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $this->authorize('dashboard:viewany');
        $cacheFlexibleDuration = [300, 3600];
        $currentOrgId = request()->get(config('pam.org.request_attribute'));

        $userCount = Cache::flexible(
            CacheKey::ORG_USERS_COUNT->key($currentOrgId),
            $cacheFlexibleDuration,
            function () use ($currentOrgId) {
                return DB::table('users')
                    ->join('org_user', 'users.id', '=', 'org_user.user_id')
                    ->where([
                        'org_user.org_id' => $currentOrgId,
                        'status' => Status::ACTIVE,
                    ])
                    ->count();
            }
        );

        $userGroupCount = Cache::flexible(
            CacheKey::ORG_USER_GROUPS_COUNT->key($currentOrgId),
            $cacheFlexibleDuration,
            function () use ($currentOrgId) {
                return DB::table('user_groups')
                    ->where([
                        'org_id' => $currentOrgId,
                        'status' => Status::ACTIVE,
                    ])
                    ->count();
            }
        );

        $assetCount = Cache::flexible(
            CacheKey::ORG_ASSETS_COUNT->key($currentOrgId),
            $cacheFlexibleDuration,
            function () use ($currentOrgId) {
                return DB::table('assets')
                    ->where([
                        'org_id' => $currentOrgId,
                        'status' => Status::ACTIVE,
                    ])
                    ->count();
            }
        );

        $pendingRequestCount = Cache::flexible(
            CacheKey::ORG_REQUESTS_PENDING_COUNT->key($currentOrgId),
            $cacheFlexibleDuration,
            function () use ($currentOrgId) {
                return DB::table('requests')
                    ->where([
                        'org_id' => $currentOrgId,
                        'status' => RequestStatus::PENDING,
                    ])
                    ->count();
            }
        );

        $requestCount = Cache::flexible(
            CacheKey::ORG_REQUESTS_COUNT->key($currentOrgId),
            $cacheFlexibleDuration,
            function () use ($currentOrgId) {
                return DB::table('requests')
                    ->where([
                        'org_id' => $currentOrgId,
                    ])
                    ->count();
            }
        );

        $scheduledSessionCount = Cache::flexible(
            CacheKey::ORG_SESSIONS_SCHEDULED_COUNT->key($currentOrgId),
            $cacheFlexibleDuration,
            function () use ($currentOrgId) {
                return DB::table('sessions')
                    ->where([
                        'org_id' => $currentOrgId,
                        'status' => SessionStatus::SCHEDULED,
                    ])
                    ->count();
            }
        );

        $activeSessionCount = Cache::flexible(
            CacheKey::ORG_SESSIONS_ACTIVE_COUNT->key($currentOrgId),
            $cacheFlexibleDuration,
            function () use ($currentOrgId) {
                return DB::table('sessions')
                    ->where([
                        'org_id' => $currentOrgId,
                        'status' => SessionStatus::STARTED,
                    ])
                    ->count();
            }
        );

        $sessionCount = Cache::flexible(
            CacheKey::ORG_SESSIONS_COUNT->key($currentOrgId),
            $cacheFlexibleDuration,
            function () use ($currentOrgId) {
                return DB::table('requests')
                    ->where([
                        'org_id' => $currentOrgId,
                    ])
                    ->count();
            }
        );

        return response()->json([
            'data' => [
                'user_count' => $userCount,
                'user_group_count' => $userGroupCount,
                'asset_count' => $assetCount,
                'pending_request_count' => $pendingRequestCount,
                'request_count' => $requestCount,
                'scheduled_session_count' => $scheduledSessionCount,
                'active_session_count' => $activeSessionCount,
                'session_count' => $sessionCount,
            ],
        ]);
    }
}
