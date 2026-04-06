<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrgAiAgent\StoreOrgAiAgentRequest;
use App\Http\Requests\OrgAiAgent\UpdateOrgAiAgentRequest;
use App\Http\Resources\OrgAiAgent\OrgAiAgentCollection;
use App\Http\Resources\OrgAiAgent\OrgAiAgentResource;
use App\Models\OrgAiAgent;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrgAiAgentController extends Controller
{
    public function index(Request $request): OrgAiAgentCollection
    {
        $this->authorize('viewAny', OrgAiAgent::class);
        $orgId = (int) $request->get(config('pam.org.request_attribute'));
        $pagination = $request->input('per_page', config('pam.pagination.per_page'));
        $agents = OrgAiAgent::query()
            ->where('org_id', $orgId)
            ->orderBy('role')
            ->paginate($pagination);

        return new OrgAiAgentCollection($agents);
    }

    public function store(StoreOrgAiAgentRequest $request): OrgAiAgentResource
    {
        $this->authorize('create', OrgAiAgent::class);
        $orgId = (int) $request->get(config('pam.org.request_attribute'));
        $data = $request->validated();
        $data['org_id'] = $orgId;
        $agent = OrgAiAgent::query()->create($data);

        return new OrgAiAgentResource($agent);
    }

    public function show(OrgAiAgent $orgAiAgent): OrgAiAgentResource
    {
        $this->authorize('view', $orgAiAgent);

        return new OrgAiAgentResource($orgAiAgent);
    }

    public function update(UpdateOrgAiAgentRequest $request, OrgAiAgent $orgAiAgent): OrgAiAgentResource
    {
        $this->authorize('update', $orgAiAgent);
        $orgAiAgent->update($request->validated());

        return new OrgAiAgentResource($orgAiAgent->fresh());
    }

    public function destroy(OrgAiAgent $orgAiAgent): Response
    {
        $this->authorize('delete', $orgAiAgent);
        $orgAiAgent->delete();

        return $this->noContent();
    }
}
