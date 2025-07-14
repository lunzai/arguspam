<?php

namespace App\Http\Controllers;

use App\Enums\CacheKey;
use App\Http\Resources\SessionAudit\SessionAuditCollection;
use App\Http\Resources\SessionAudit\SessionAuditResource;
use App\Models\SessionAudit;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SessionAuditController extends Controller
{
    use IncludeRelationships;

    # TODO: Change to nested controller and route
    public function index(Request $request): SessionAuditCollection
    {
        $this->authorize('viewAny', SessionAudit::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        // $sessionAudits = Cache::remember(
        //     CacheKey::SESSION_AUDITS->value,
        //     config('cache.default_ttl'),
        //     function () use ($pagination) {
        //         return SessionAudit::paginate($pagination);
        //     }
        // );
        $sessionAudits = SessionAudit::paginate($pagination);
        return new SessionAuditCollection($sessionAudits);
    }

    public function show(SessionAudit $sessionAudit): SessionAuditResource
    {
        $sessionAuditQuery = SessionAudit::query();
        $this->applyIncludes($sessionAuditQuery, request());
        $sessionAudit = $sessionAuditQuery->findOrFail($sessionAudit->id);
        $this->authorize('view', $sessionAudit);

        return new SessionAuditResource($sessionAudit);
    }
}
