<?php

namespace App\Http\Resources\User;

use App\Http\Resources\AssetAccessGrant\AssetAccessGrantResource;
use App\Http\Resources\Org\OrgResource;
use App\Http\Resources\Request\RequestResource;
use App\Http\Resources\Resource;
use App\Http\Resources\Session\SessionResource;
use App\Http\Resources\UserGroup\UserGroupResource;
use Illuminate\Http\Request;

class MeResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'attributes' => [
                'id' => $this->id,
                'name' => e($this->name),
                'email' => e($this->email),
                'default_timezone' => $this->default_timezone,
                'email_verified_at' => $this->email_verified_at,
                'two_factor_enabled' => $this->two_factor_enabled,
                'two_factor_confirmed_at' => $this->two_factor_confirmed_at,
                'last_login_at' => $this->last_login_at,
                'status' => $this->status,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
                'roles' => $this->roles->map(function ($role) {
                    return [
                        'id' => $role->id,
                        'name' => e($role->name),
                        'description' => e($role->description),
                        'is_default' => $role->is_default,
                    ];
                }),
                'permissions' => $this->permissions
                    ->select(['id', 'name', 'description'])
                    ->groupBy(fn($item) => explode(':', $item['name'])[0])
                    ->map(fn($group) => [
                        'group' => explode(':', $group->first()['name'])[0],
                        'data' => $group->values()->all(),
                    ])
                    ->values(),
                'scheduled_sessions_count' => $this->scheduledSessions->count(),
                'submitted_requests_count' => $this->submittedRequests->count(),
                'orgs' => $this->orgs->select(['id', 'name', 'description', 'status']),
                'user_groups' => $this->userGroups->select(['id', 'org_id', 'name', 'description', 'status']),
            ],
        ];
    }
}
