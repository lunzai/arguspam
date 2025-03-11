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
        $sessionAudit = SessionAudit::query();
        // $this->applyExpands($sessionAudit);

        return new SessionAuditCollection(
            $sessionAudit->paginate(config('pam.pagination.per_page'))
        );
    }

    public function show(string $id): SessionAuditResource
    {
        $sessionAudit = SessionAudit::query();
        $this->applyIncludes($sessionAudit, request());

        return new SessionAuditResource($sessionAudit->findOrFail($id));
    }
}
