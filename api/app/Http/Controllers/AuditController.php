<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Filters\ActionAuditFilter;
use App\Http\Resources\ActionAudit\ActionAuditCollection;
use App\Http\Resources\ActionAudit\ActionAuditResource;
use App\Models\ActionAudit;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class AuditController extends Controller
{
    use IncludeRelationships;

    public function index(ActionAuditFilter $filter, Request $request): ActionAuditCollection
    {
        $this->authorize('viewAny', ActionAudit::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $actionAudits = Cache::remember(
            CacheKey::ACTION_AUDITS->value,
            config('cache.default_ttl'),
            function () use ($filter, $pagination) {
                return ActionAudit::filter($filter)->paginate($pagination);
            }
        );
        return new ActionAuditCollection($actionAudits);
    }

    public function show(string $id): ActionAuditResource
    {
        $actionAuditQuery = ActionAudit::query();
        $this->applyIncludes($actionAuditQuery, request());
        $actionAudit = $actionAuditQuery->findOrFail($id);
        $this->authorize('view', $actionAudit);

        return new ActionAuditResource($actionAudit);
    }
}
