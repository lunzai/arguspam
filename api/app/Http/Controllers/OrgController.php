<?php

namespace App\Http\Controllers;

use App\Http\Requests\Org\StoreOrgRequest;
use App\Http\Requests\Org\UpdateOrgRequest;
use App\Http\Resources\Org\OrgCollection;
use App\Http\Resources\Org\OrgResource;
use App\Models\Org;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrgController extends Controller
{
    use IncludeRelationships;

    public function index(): OrgCollection
    {
        $org = Org::query();

        return new OrgCollection(
            Org::paginate(config('pam.pagination.per_page'))
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

    public function update(UpdateOrgRequest $request, string $id): OrgResource
    {
        $org = Org::findOrFail($id);
        $validated = $request->validated();
        $org->update($validated);

        return new OrgResource($org);
    }

    public function destroy(string $id): Response
    {
        $org = Org::findOrFail($id);
        $org->deleted_by = Auth::id();
        $org->save();
        $org->delete();

        return response()->noContent();
    }
}
