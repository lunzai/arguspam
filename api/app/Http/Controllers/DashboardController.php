<?php

namespace App\Http\Controllers;

use App\Enums\RequestStatus;
use App\Enums\SessionStatus;
use App\Enums\Status;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $currentOrgId = request()->current_org_id;
        $userCount = DB::table('users')
            ->join('org_user', 'users.id', '=', 'org_user.user_id')
            ->where([
                'org_user.org_id' => $currentOrgId,
                'status' => Status::ACTIVE,
            ])
            ->count();
        $userGroupCount = DB::table('user_groups')
            ->where([
                'org_id' => $currentOrgId,
                'status' => Status::ACTIVE,
            ])
            ->count();
        $assetCount = DB::table('assets')
            ->where([
                'org_id' => $currentOrgId,
                'status' => Status::ACTIVE,
            ])
            ->count();
        $pendingRequestCount = DB::table('requests')
            ->where([
                'org_id' => $currentOrgId,
                'status' => RequestStatus::PENDING,
            ])
            ->count();
        $requestCount = DB::table('requests')
            ->where([
                'org_id' => $currentOrgId,
            ])
            ->count();
        $scheduledSessionCount = DB::table('sessions')
            ->where([
                'org_id' => $currentOrgId,
                'status' => SessionStatus::SCHEDULED,
            ])
            ->count();
        $activeSessionCount = DB::table('sessions')
            ->where([
                'org_id' => $currentOrgId,
                'status' => SessionStatus::ACTIVE,
            ])
            ->count();
        $sessionCount = DB::table('requests')
            ->where([
                'org_id' => $currentOrgId,
            ])
            ->count();
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
