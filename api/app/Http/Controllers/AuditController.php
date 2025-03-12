<?php

namespace App\Http\Controllers;

use App\Http\Resources\ActionAudit\ActionAuditCollection;
use App\Http\Resources\ActionAudit\ActionAuditResource;
use App\Http\Filters\ActionAuditFilter;
use App\Models\ActionAudit;
use App\Traits\IncludeRelationships;

class AuditController extends Controller
{
    use IncludeRelationships;

    public function index(ActionAuditFilter $filter): ActionAuditCollection
    {
        $actionAudit = ActionAudit::filter($filter);

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
