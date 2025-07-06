<?php

namespace App\Http\Controllers;

use App\Http\Filters\RoleFilter;
use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Http\Resources\Role\RoleCollection;
use App\Http\Resources\Role\RoleResource;
use App\Models\Role;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Response;

class RoleController extends Controller
{
    use IncludeRelationships;

    /**
     * Display a listing of the resource.
     */
    public function index(RoleFilter $filter): RoleCollection
    {
        $this->authorize('viewAny', Role::class);
        $roles = Role::filter($filter);

        return new RoleCollection(
            $roles->paginate(config('pam.pagination.per_page'))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRoleRequest $request): RoleResource
    {
        $this->authorize('create', Role::class);
        $validated = $request->validated();
        $role = Role::create($validated);

        return new RoleResource($role);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): RoleResource
    {
        $roleQuery = Role::query();
        $this->applyIncludes($roleQuery, request());
        $role = $roleQuery->findOrFail($id);
        $this->authorize('view', $role);

        return new RoleResource($role);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role): RoleResource
    {
        $this->authorize('update', $role);
        if ($role->is_default) {
            $this->unprocessableEntity('Cannot update default role.');
        }
        $validated = $request->validated();
        $role->update($validated);
        return new RoleResource($role);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role): Response
    {
        $this->authorize('delete', $role);
        if ($role->is_default) {
            $this->unprocessableEntity('Cannot delete default role.');
        }
        if ($role->users()->exists()) {
            $this->unprocessableEntity('Cannot delete role with assigned users.');
        }
        $role->delete();
        return $this->noContent();
    }
}
