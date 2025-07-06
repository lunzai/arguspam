<?php

namespace App\Http\Controllers;

use App\Http\Filters\ActionAuditFilter;
use App\Http\Resources\ActionAudit\ActionAuditCollection;
use App\Http\Resources\ActionAudit\ActionAuditResource;
use App\Models\ActionAudit;
use App\Traits\IncludeRelationships;

class AuditController extends Controller
{
    use IncludeRelationships;

    public function index(ActionAuditFilter $filter): ActionAuditCollection
    {
        $this->authorize('viewAny', ActionAudit::class);
        $actionAudit = ActionAudit::filter($filter);

        return new ActionAuditCollection(
            ActionAudit::paginate(config('pam.pagination.per_page'))
        );
    }

    public function show(string $id): ActionAuditResource
    {
        $actionAuditQuery = ActionAudit::query();
        $this->applyIncludes($actionAuditQuery, request());
        $actionAudit = $actionAuditQuery->findOrFail($id);
        $this->authorize('view', $actionAudit);

        return new ActionAuditResource($actionAudit);
    }
}
