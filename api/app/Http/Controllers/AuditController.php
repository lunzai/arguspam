<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActionAudit\ActionAuditCollection;
use App\Http\Resources\ActionAudit\ActionAuditResource;
use App\Models\ActionAudit;
use App\Traits\IncludeRelationships;

class AuditController extends Controller
{
    use IncludeRelationships;

    public function index(): ActionAuditCollection
    {
        $actionAudit = ActionAudit::query();
        // $this->applyExpands($actionAudit);

        return new ActionAuditCollection(
            ActionAudit::paginate(config('pam.pagination.per_page'))
        );
    }

    public function show(string $id): ActionAuditResource
    {
        $actionAudit = ActionAudit::query();
        $this->applyIncludes($actionAudit, request());

        return new ActionAuditResource($actionAudit->findOrFail($id));
    }
}
