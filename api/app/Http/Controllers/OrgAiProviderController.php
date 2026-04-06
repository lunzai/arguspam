<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrgAiProvider\StoreOrgAiProviderRequest;
use App\Http\Requests\OrgAiProvider\UpdateOrgAiProviderRequest;
use App\Http\Resources\OrgAiProvider\OrgAiProviderCollection;
use App\Http\Resources\OrgAiProvider\OrgAiProviderResource;
use App\Models\OrgAiProvider;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class OrgAiProviderController extends Controller
{
    public function index(Request $request): OrgAiProviderCollection
    {
        $this->authorize('viewAny', OrgAiProvider::class);
        $orgId = (int) $request->get(config('pam.org.request_attribute'));
        $pagination = $request->input('per_page', config('pam.pagination.per_page'));
        $providers = OrgAiProvider::query()
            ->where('org_id', $orgId)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate($pagination);

        return new OrgAiProviderCollection($providers);
    }

    public function store(StoreOrgAiProviderRequest $request): OrgAiProviderResource
    {
        $this->authorize('create', OrgAiProvider::class);
        $orgId = (int) $request->get(config('pam.org.request_attribute'));
        $data = $request->validated();
        $data['org_id'] = $orgId;
        if (!array_key_exists('sort_order', $data)) {
            $data['sort_order'] = (int) (OrgAiProvider::query()->where('org_id', $orgId)->max('sort_order') ?? -1) + 1;
        }
        if (!array_key_exists('is_enabled', $data)) {
            $data['is_enabled'] = true;
        }
        $provider = OrgAiProvider::query()->create($data);

        return new OrgAiProviderResource($provider);
    }

    public function show(OrgAiProvider $orgAiProvider): OrgAiProviderResource
    {
        $this->authorize('view', $orgAiProvider);

        return new OrgAiProviderResource($orgAiProvider);
    }

    public function update(UpdateOrgAiProviderRequest $request, OrgAiProvider $orgAiProvider): OrgAiProviderResource
    {
        $this->authorize('update', $orgAiProvider);
        $data = $request->validated();
        if (array_key_exists('api_key', $data) && ($data['api_key'] === null || $data['api_key'] === '')) {
            unset($data['api_key']);
        }
        $orgAiProvider->update($data);

        return new OrgAiProviderResource($orgAiProvider->fresh());
    }

    public function destroy(OrgAiProvider $orgAiProvider): Response
    {
        $this->authorize('delete', $orgAiProvider);
        $orgAiProvider->delete();

        return $this->noContent();
    }
}
