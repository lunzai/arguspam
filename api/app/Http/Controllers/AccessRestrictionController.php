<?php

namespace App\Http\Controllers;

use App\Http\Filters\AccessRestrictionFilter;
use App\Http\Requests\AccessRestriction\StoreAccessRestrictionRequest;
use App\Http\Requests\AccessRestriction\UpdateAccessRestrictionRequest;
use App\Http\Resources\AccessRestriction\AccessRestrictionCollection;
use App\Http\Resources\AccessRestriction\AccessRestrictionResource;
use App\Models\AccessRestriction;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;

class AccessRestrictionController extends Controller
{
    use IncludeRelationships;

    public function index(AccessRestrictionFilter $filter, Request $request): AccessRestrictionCollection
    {
        $this->authorize('view', AccessRestriction::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $accessRestrictions = AccessRestriction::filter($filter)
            ->paginate($pagination);
        return new AccessRestrictionCollection($accessRestrictions);
    }

    public function store(StoreAccessRestrictionRequest $request): AccessRestrictionResource
    {
        $this->authorize('create', AccessRestriction::class);
        $validated = $request->validated();
        $accessRestriction = AccessRestriction::create($validated);

        return new AccessRestrictionResource($accessRestriction);
    }

    public function show(string $id): AccessRestrictionResource
    {
        $accessRestrictionQuery = AccessRestriction::query();
        $this->applyIncludes($accessRestrictionQuery, request());
        $accessRestriction = $accessRestrictionQuery->findOrFail($id);
        $this->authorize('view', $accessRestriction);

        return new AccessRestrictionResource($accessRestriction);
    }

    public function update(UpdateAccessRestrictionRequest $request, AccessRestriction $accessRestriction): AccessRestrictionResource
    {
        $this->authorize('update', $accessRestriction);
        $validated = $request->validated();
        $accessRestriction->update($validated);

        return new AccessRestrictionResource($accessRestriction);
    }

    public function destroy(AccessRestriction $accessRestriction)
    {
        $this->authorize('delete', $accessRestriction);
        if ($accessRestriction->users()->exists()) {
            return $this->unprocessableEntity('Cannot delete access restriction with assigned users.');
        }
        if ($accessRestriction->userGroups()->exists()) {
            return $this->unprocessableEntity('Cannot delete access restriction with assigned user groups.');
        }
        $accessRestriction->delete();
        return $this->noContent();
    }
}
