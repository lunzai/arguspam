<?php

namespace App\Http\Controllers;

use App\Http\Resources\SessionAudit\SessionAuditCollection;
use App\Http\Resources\SessionAudit\SessionAuditResource;
use App\Models\SessionAudit;
use App\Traits\IncludesRelationships;

class SessionAuditController extends Controller
{
    use IncludesRelationships;

    public function index(): SessionAuditCollection
    {
        $sessionAudit = SessionAudit::query();
        // $this->applyExpands($sessionAudit);

        return new SessionAuditCollection(
            $sessionAudit->paginate(config('constants.pagination.per_page'))
        );
    }

    public function show(string $id): SessionAuditResource
    {
        $sessionAudit = SessionAudit::query();
        $this->applyIncludes($sessionAudit, request());

        return new SessionAuditResource($sessionAudit->findOrFail($id));
    }
}
