<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActionAudit\ActionAuditCollection;
use App\Http\Resources\ActionAudit\ActionAuditResource;
use App\Models\ActionAudit;
use App\Traits\IsExpandable;

class AuditController extends Controller
{
    use IsExpandable;

    public function index()
    {
        $actionAudit = ActionAudit::query();
        $this->applyExpands($actionAudit);
        return new ActionAuditCollection(
            ActionAudit::paginate(config('constants.pagination.per_page'))
        );
    }

    public function show(string $id)
    {
        $actionAudit = ActionAudit::query();
        $this->applyExpands($actionAudit);
        return new ActionAuditResource($actionAudit->findOrFail($id));
    }
}
