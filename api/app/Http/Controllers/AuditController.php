<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActionAudit\ActionAuditCollection;
use App\Http\Resources\ActionAudit\ActionAuditResource;
use App\Models\ActionAudit;
use App\Traits\IncludesRelationships;

class AuditController extends Controller
{
    use IncludesRelationships;

    public function index(): ActionAuditCollection
    {
        $actionAudit = ActionAudit::query();
        // $this->applyExpands($actionAudit);

        return new ActionAuditCollection(
            ActionAudit::paginate(config('constants.pagination.per_page'))
        );
    }

    public function show(string $id): ActionAuditResource
    {
        $actionAudit = ActionAudit::query();
        $this->applyIncludes($actionAudit, request());

        return new ActionAuditResource($actionAudit->findOrFail($id));
    }
}
