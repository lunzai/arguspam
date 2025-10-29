<?php

namespace App\Http\Controllers;

use App\Http\Filters\PermissionFilter;
use App\Http\Resources\Permission\PermissionCollection;
use App\Http\Resources\Permission\PermissionResource;
use App\Models\Permission;
use App\Traits\IncludeRelationships;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    use IncludeRelationships;

    public function index(PermissionFilter $filter, Request $request): PermissionCollection
    {
        $this->authorize('view', Permission::class);
        $pagination = $request->get('per_page', config('pam.pagination.per_page'));
        $permissions = Permission::filter($filter)->paginate($pagination);
        return new PermissionCollection($permissions);
    }

    public function show(string $id): PermissionResource
    {
        $permissionQuery = Permission::query();
        $this->applyIncludes($permissionQuery, request());
        $permission = $permissionQuery->findOrFail($id);
        $this->authorize('view', $permission);

        return new PermissionResource($permission);
    }
}
