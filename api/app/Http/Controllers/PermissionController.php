<?php

namespace App\Http\Controllers;

use App\Http\Filters\PermissionFilter;
use App\Http\Requests\Permission\StorePermissionRequest;
use App\Http\Requests\Permission\UpdatePermissionRequest;
use App\Http\Resources\Permission\PermissionCollection;
use App\Http\Resources\Permission\PermissionResource;
use App\Models\Permission;
use App\Traits\IncludeRelationships;

class PermissionController extends Controller
{
    use IncludeRelationships;

    /**
     * Display a listing of the resource.
     */
    public function index(PermissionFilter $filter): PermissionCollection
    {
        $this->authorize('viewAny', Permission::class);
        $permissions = Permission::filter($filter);

        return new PermissionCollection(
            $permissions->paginate(config('pam.pagination.per_page'))
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePermissionRequest $request): PermissionResource
    {
        $this->authorize('create', Permission::class);
        $validated = $request->validated();
        $permission = Permission::create($validated);

        return new PermissionResource($permission);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): PermissionResource
    {
        $permissionQuery = Permission::query();
        $this->applyIncludes($permissionQuery, request());
        $permission = $permissionQuery->findOrFail($id);
        $this->authorize('view', $permission);

        return new PermissionResource($permission);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePermissionRequest $request, Permission $permission): PermissionResource
    {
        $this->authorize('update', $permission);
        $validated = $request->validated();
        $permission->update($validated);

        return new PermissionResource($permission);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $this->authorize('delete', $permission);
        if ($permission->roles()->exists()) {
            return $this->unprocessableEntity('Cannot delete permission with assigned roles.');
        }
        $permission->delete();
        return $this->noContent();
    }
}
