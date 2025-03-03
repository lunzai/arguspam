<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\ActionAudit\ActionAuditCollection;
use App\Http\Resources\ActionAudit\ActionAuditResource;
use App\Models\ActionAudit;

class AuditController extends Controller
{
    public function index()
    {
        return new ActionAuditCollection(
            ActionAudit::paginate(config('constants.pagination.per_page'))
        );
    }

    public function show(string $id)
    {
        return new ActionAuditResource(ActionAudit::findOrFail($id));
    }
}
