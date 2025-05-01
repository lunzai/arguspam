<?php

namespace App\Http\Controllers;

use App\Http\Requests\Role\StoreRoleRequest;
use App\Http\Requests\Role\UpdateRoleRequest;
use App\Models\Role;
use App\Http\Resources\Role\RoleCollection;
use App\Http\Resources\Role\RoleResource;
use App\Http\Filters\RoleFilter;
use Illuminate\Http\Response;

class RoleController extends Controller
{
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
    public function show(Role $role) : RoleResource
    {
        $role = Role::query();
        $this->applyIncludes($role, request());

        return new RoleResource($role->findOrFail($role->id));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateRoleRequest $request, Role $role) : RoleResource
    {
        $validated = $request->validated();
        $role->update($validated);
        return new RoleResource($role);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role) : Response
    {
        if ($role->users()->exists()) {
            return $this->error('Cannot delete role with assigned users.', 422); // 422 Unprocessable Entity
        }
        $role->delete();
        return response()->noContent();
    }
}
