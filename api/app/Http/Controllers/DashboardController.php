<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $currentOrgId = $request->get(config('pam.org.request_attribute'));
        $dashboardService = new DashboardService($currentOrgId);
        $cacheFlexibleDuration = [5, 3600];

        $data = Cache::flexible(
            CacheKey::DASHBOARD->key($currentOrgId),
            $cacheFlexibleDuration,
            function () use ($dashboardService) {
                return [
                    'user_count' => $dashboardService->getUserCount(),
                    'user_group_count' => $dashboardService->getUserGroupCount(),
                    'asset_count' => $dashboardService->getAssetCount(),
                    'request_count' => $dashboardService->getRequestCount(),
                    'session_count' => $dashboardService->getSessionCount(),
                    'request_status_count' => $dashboardService->getRequestStatusCount(90),
                    'session_status_count' => $dashboardService->getSessionStatusCount(90),
                    'session_flag_count' => $dashboardService->getSessionFlagCount(90),
                    'asset_distribution' => $dashboardService->getAssetDistribution(),
                    'request_scope_distribution' => $dashboardService->getRequestScopeDistribution(),
                    'request_approver_risk_rating_distribution' => $dashboardService->getRequestApproverRiskRatingDistribution(),
                    'session_audit_flag_distribution' => $dashboardService->getSessionAuditFlagDistribution(),
                ];
            }
        );

        return response()->json([
            'data' => $data,
        ]);
    }
}
