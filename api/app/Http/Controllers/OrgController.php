<?php

namespace App\Http\Controllers;

use App\Http\Requests\Org\StoreOrgRequest;
use App\Http\Requests\Org\UpdateOrgRequest;
use App\Http\Resources\Org\OrgCollection;
use App\Http\Resources\Org\OrgResource;
use App\Models\Org;
use Illuminate\Support\Facades\Auth;
use App\Traits\IsExpandable;

class OrgController extends Controller
{
    use IsExpandable;

    public function index()
    {
        $org = Org::query();
        $this->applyExpands($org);
        return new OrgCollection(
            Org::paginate(config('constants.pagination.per_page'))
        );
    }

    public function store(StoreOrgRequest $request)
    {
        $validated = $request->validated();
        $validated['created_by'] = Auth::id();
        $org = Org::create($validated);

        return new OrgResource($org);
    }

    public function show(string $id)
    {
        $org = Org::query();
        $this->applyExpands($org);
        return new OrgResource($org->findOrFail($id));
    }

    public function update(UpdateOrgRequest $request, string $id)
    {
        $org = Org::findOrFail($id);
        $validated = $request->validated();
        $validated['updated_by'] = Auth::id();
        $org->update($validated);

        return new OrgResource($org);
    }

    public function destroy(string $id)
    {
        $org = Org::findOrFail($id);
        $org->deleted_by = Auth::id();
        $org->save();
        $org->delete();

        return response()->noContent();
    }
}
