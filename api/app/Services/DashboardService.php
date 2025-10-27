<?php

namespace App\Services;

use App\Models\Asset;
use App\Models\Request as RequestModel;
use App\Models\Session;
use App\Models\SessionFlag;
use App\Models\User;
use App\Models\UserGroup;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Str;

class DashboardService
{
    public function __construct(private int $orgId) {}

    public function getUserCount(): int
    {
        return User::join('org_user', 'users.id', '=', 'org_user.user_id')
            ->where('org_user.org_id', $this->orgId)
            ->active()
            ->count();
    }

    public function getUserGroupCount(): int
    {
        return UserGroup::where('org_id', $this->orgId)
            ->active()
            ->count();
    }

    public function getAssetCount(): int
    {
        return Asset::where('org_id', $this->orgId)
            ->active()
            ->count();
    }

    public function getRequestCount(): int
    {
        return RequestModel::where('org_id', $this->orgId)
            ->count();
    }

    public function getSessionCount(): int
    {
        return Session::where('org_id', $this->orgId)
            ->count();
    }

    public function getAssetDistribution(): array
    {
        $data = Asset::where('org_id', $this->orgId)
            ->select(DB::raw('dbms as label, COUNT(*) as count'))
            ->groupBy('dbms')
            ->get()
            ->map(function ($row) {
                return [
                    'x' => str_replace('sql', 'SQL', Str::title($row->label)),
                    'y' => $row->count,
                ];
            })
            ->toArray();
        return ['data' => $data];

    }

    public function getRequestScopeDistribution(): array
    {
        $data = RequestModel::where('org_id', $this->orgId)
            ->select(DB::raw('scope as label, COUNT(*) as count'))
            ->groupBy('scope')
            ->get()
            ->map(function ($row) {
                return [
                    'x' => $row->label,
                    'y' => $row->count,
                ];
            })
            ->toArray();
        return ['data' => $data];
    }

    public function getRequestApproverRiskRatingDistribution(): array
    {
        $data = RequestModel::where('org_id', $this->orgId)
            ->whereNotNull('approver_risk_rating')
            ->select(DB::raw('approver_risk_rating as label, COUNT(*) as count'))
            ->groupBy('approver_risk_rating')
            ->get()
            ->map(function ($row) {
                return [
                    'x' => Str::title($row->label),
                    'y' => $row->count,
                ];
            })
            ->toArray();
        return ['data' => $data];
    }

    public function getSessionAuditFlagDistribution(): array
    {
        $data = SessionFlag::where('org_id', $this->orgId)
            ->join('sessions', 'session_flags.session_id', '=', 'sessions.id')
            ->select(DB::raw('session_flags.flag as label, COUNT(*) as count'))
            ->groupBy('session_flags.flag')
            ->get()
            ->map(function ($row) {
                return [
                    'x' => Str::title($row->label),
                    'y' => $row->count,
                ];
            })
            ->toArray();
        return ['data' => $data];
    }

    public function getRequestStatusCount(int $days = 30): array
    {
        $data = RequestModel::where('org_id', $this->orgId)
            ->select(DB::raw('DATE(submitted_at) as date, status as label, COUNT(*) as count'))
            ->where('submitted_at', '>=', now()->subDays($days))
            ->groupBy('date', 'status')
            ->get();
        $statusLabels = $data
            ->pluck('label')
            ->unique()
            ->toArray();
        return $this->fillMissingData($data->toArray(), $statusLabels, $days);
    }

    public function getSessionStatusCount(int $days = 30): array
    {
        $data = Session::where('org_id', $this->orgId)
            ->select(DB::raw('DATE(created_at) as date, status as label, COUNT(*) as count'))
            ->where('created_at', '>=', now()->subDays($days))
            ->groupBy('date', 'status')
            ->get();
        $statusLabels = $data
            ->pluck('label')
            ->unique()
            ->toArray();
        return $this->fillMissingData($data->toArray(), $statusLabels, $days);
    }

    public function getSessionFlagCount(int $days = 30): array
    {
        $data = SessionFlag::where('org_id', $this->orgId)
            ->join('sessions', 'session_flags.session_id', '=', 'sessions.id')
            ->select(DB::raw('DATE(sessions.ai_reviewed_at) as date, session_flags.flag as label, COUNT(*) as count'))
            ->where('sessions.ai_reviewed_at', '>=', now()->subDays($days))
            ->groupBy('date', 'label')
            ->get();
        $statusLabels = $data
            ->pluck('label')
            ->unique()
            ->toArray();
        return $this->fillMissingData($data->toArray(), $statusLabels, $days);
    }

    private function fillMissingData(array $data, array $labels, int $days): array
    {
        $result = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            foreach ($labels as $label) {
                if (!isset($result[Str::camel($label)])) {
                    $result[Str::camel($label)] = [
                        'name' => Str::title($label),
                        'data' => [],
                    ];
                }
                $result[Str::camel($label)]['data'][$date] = [
                    'x' => $date,
                    'y' => 0,
                ];
            }
        }
        foreach ($data as $item) {
            $result[Str::camel($item['label'])]['data'][$item['date']]['y'] = $item['count'];
        }
        $result = array_map(function ($item) {
            $item['data'] = array_values($item['data']);
            return $item;
        }, $result);
        return array_values($result);
    }
}
