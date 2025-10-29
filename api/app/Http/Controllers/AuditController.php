<?php

namespace App\Http\Controllers;

use App\Http\Filters\ActionAuditFilter;
use App\Http\Resources\ActionAudit\ActionAuditCollection;
use App\Http\Resources\ActionAudit\ActionAuditResource;
use App\Models\ActionAudit;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    use IncludeRelationships;

    public function index(ActionAuditFilter $filter, Request $request): ActionAuditCollection
    {
        $this->authorize('view', ActionAudit::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $actionAudits = ActionAudit::filter($filter)
            ->paginate($pagination);
        return new ActionAuditCollection($actionAudits);
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
