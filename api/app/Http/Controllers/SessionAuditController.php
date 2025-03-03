<?php

namespace App\Http\Controllers;

use App\Http\Resources\SessionAudit\SessionAuditCollection;
use App\Http\Resources\SessionAudit\SessionAuditResource;
use App\Models\SessionAudit;

class SessionAuditController extends Controller
{
    public function index()
    {
        return new SessionAuditCollection(
            SessionAudit::paginate(config('constants.pagination.per_page'))
        );
    }

    public function show(string $id)
    {
        return new SessionAuditResource(SessionAudit::findOrFail($id));
    }
}
