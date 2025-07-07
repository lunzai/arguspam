<?php

namespace App\Http\Controllers;

use App\Http\Filters\OrgFilter;
use App\Http\Requests\Org\StoreOrgRequest;
use App\Http\Requests\Org\UpdateOrgRequest;
use App\Http\Resources\Org\OrgCollection;
use App\Http\Resources\Org\OrgResource;
use App\Models\Org;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OrgController extends Controller
{
    use IncludeRelationships;

    public function index(OrgFilter $filter, Request $request): OrgCollection
    {
        $this->authorize('viewAny', Org::class);
        $org = Org::filter($filter);

        return new OrgCollection(
            $org->paginate(config('pam.pagination.per_page'))
        );
    }

    public function store(StoreOrgRequest $request): OrgResource
    {
        $this->authorize('create', Org::class);
        $validated = $request->validated();
        $org = Org::create($validated);

        return new OrgResource($org);
    }

    public function show(string $id): OrgResource
    {

        $orgQuery = Org::query();
        $this->applyIncludes($orgQuery, request());
        $org = $orgQuery->findOrFail($id);
        $this->authorize('view', $org);

        return new OrgResource($org);
    }

    public function update(UpdateOrgRequest $request, Org $org): OrgResource
    {
        $this->authorize('update', $org);
        $validated = $request->validated();
        $org->update($validated);

        return new OrgResource($org);
    }

    public function destroy(Org $org): Response
    {
        $this->authorize('delete', $org);
        $org->deleted_by = Auth::id();
        $org->save();
        $org->delete();

        return $this->noContent();
    }
}
