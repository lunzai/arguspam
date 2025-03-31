<?php

namespace App\Http\Controllers;

use App\Http\Filters\OrgFilter;
use App\Http\Requests\Org\StoreOrgRequest;
use App\Http\Requests\Org\UpdateOrgRequest;
use App\Http\Resources\Org\OrgCollection;
use App\Http\Resources\Org\OrgResource;
use App\Models\Org;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class OrgController extends Controller
{
    use IncludeRelationships;

    public function index(OrgFilter $filter): OrgCollection
    {
        $org = Org::filter($filter);

        return new OrgCollection(
            $org->paginate(config('pam.pagination.per_page'))
        );
    }

    public function store(StoreOrgRequest $request): OrgResource
    {
        $validated = $request->validated();
        $org = Org::create($validated);

        return new OrgResource($org);
    }

    public function show(string $id): OrgResource
    {
        $org = Org::query();
        $this->applyIncludes($org, request());

        return new OrgResource($org->findOrFail($id));
    }

    public function update(UpdateOrgRequest $request, Org $org): OrgResource
    {
        $validated = $request->validated();
        $org->update($validated);

        return new OrgResource($org);
    }

    public function destroy(Org $org): Response
    {
        $org->deleted_by = Auth::id();
        $org->save();
        $org->delete();

        return response()->noContent();
    }
}
