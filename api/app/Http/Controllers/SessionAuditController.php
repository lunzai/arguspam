<?php

namespace App\Http\Controllers;

use App\Http\Resources\SessionAudit\SessionAuditCollection;
use App\Http\Resources\SessionAudit\SessionAuditResource;
use App\Models\SessionAudit;
use App\Traits\IncludeRelationships;

class SessionAuditController extends Controller
{
    use IncludeRelationships;

    public function index(): SessionAuditCollection
    {
        $this->authorize('viewAny', SessionAudit::class);
        $sessionAudit = SessionAudit::query();

        return new SessionAuditCollection(
            $sessionAudit->paginate(config('pam.pagination.per_page'))
        );
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
