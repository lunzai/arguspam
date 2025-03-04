<?php

namespace App\Http\Controllers;

use App\Http\Resources\SessionAudit\SessionAuditCollection;
use App\Http\Resources\SessionAudit\SessionAuditResource;
use App\Models\SessionAudit;
use App\Traits\IsExpandable;

class SessionAuditController extends Controller
{
    use IsExpandable;

    public function index()
    {
        $sessionAudit = SessionAudit::query();
        $this->applyExpands($sessionAudit);
        return new SessionAuditCollection(
            $sessionAudit->paginate(config('constants.pagination.per_page'))
        );
    }

    public function show(string $id)
    {
        $sessionAudit = SessionAudit::query();
        $this->applyExpands($sessionAudit);
        return new SessionAuditResource($sessionAudit->findOrFail($id));
    }
}
