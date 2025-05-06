<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Role;
use App\Http\Resources\Role\RoleCollection;
use App\Http\Resources\Role\RoleResource;
use App\Http\Filters\RoleFilter;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Response;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Validation\ValidationException;

class RoleController extends Controller
{
    use IncludeRelationships;

    /**
     * Display a listing of the resource.
     */
    public function index(RoleFilter $filter): RoleCollection
    {
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
        $validated = $request->validated();
        $role = Role::create($validated);

        return new RoleResource($role);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) : RoleResource
    {
        $role = Role::query();
        $this->applyIncludes($role, request());

        return new RoleResource($role->findOrFail($id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role) : RoleResource
    {
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
    public function destroy(Role $role) : Response
    {
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
