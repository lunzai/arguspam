<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AssetAccessRole;
use App\Enums\Status;
use App\Http\Filters\QueryFilter;
use App\Traits\HasBlamable;
use App\Traits\HasRbac;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasBlamable, HasFactory,
        HasRbac, HasStatus, Notifiable;

    protected $fillable = [
        'name',
        'email',
        // 'password',
        'status',
        // 'two_factor_enabled',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
        'deleted_by',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public static $includable = [
        'orgs',
        'userGroups',
        'assetAccessGrants',
        'approverAssetAccessGrants',
        'requesterAssetAccessGrants',
        'requests',
        'sessions',
        'accessRestrictions',
        'roles',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'last_login_at' => 'datetime',
            'status' => Status::class,
        ];
    }

    public static $attributeLabels = [
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'status' => 'Status',
        'two_factor_enabled' => 'MFA',
        'last_login_at' => 'Last Login At',
    ];

    public function inOrg(Org $org): bool
    {
        return $this->orgs->contains($org);
    }

    public function scopeFilter(Builder $builder, QueryFilter $filter)
    {
        return $filter->apply($builder);
    }

    public function orgs(): BelongsToMany
    {
        return $this->belongsToMany(Org::class);
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_user_group');
    }

    public function assetAccessGrants(): HasMany
    {
        return $this->hasMany(AssetAccessGrant::class);
    }

    // public function approverAssetAccessGrants(): HasMany
    // {
    //     return $this->assetAccessGrants()
    //         ->where('role', AssetAccessRole::APPROVER->value);
    // }

    // public function requesterAssetAccessGrants(): HasMany
    // {
    //     return $this->assetAccessGrants()
    //         ->where('role', AssetAccessRole::REQUESTER->value);
    // }

    public function requesterAssets(): BelongsToMany
    {
        return $this->belongsToMany(
            Asset::class,
            'asset_access_grants',
        )->where('asset_access_grants.role', AssetAccessRole::REQUESTER->value);
    }

    public function approverAssets(): BelongsToMany
    {
        return $this->belongsToMany(
            Asset::class,
            'asset_access_grants',
        )->where('asset_access_grants.role', AssetAccessRole::APPROVER->value);
    }

    public function allRequesterAssets(): Builder
    {
        return $this->getAssetByRole(AssetAccessRole::REQUESTER);
    }

    public function allApproverAssets(): Builder
    {
        return $this->getAssetByRole(AssetAccessRole::APPROVER);
    }

    public function allAssets(): Builder
    {
        return Asset::where(function ($query) {
            // Direct user access
            $query->whereHas('accessGrants', function ($q) {
                $q->where('user_id', $this->id);
            });
        })->orWhere(function ($query) {
            // Access via groups
            $query->whereHas('accessGrants', function ($q) {
                $q->whereIn('user_group_id', $this->userGroups->pluck('id'));
            });
        })->distinct();
    }

    private function getAssetByRole(AssetAccessRole $role): Builder
    {
        return Asset::where(function ($query) use ($role) {
            // Direct user access
            $query->whereHas('accessGrants', function ($q) use ($role) {
                $q->where('user_id', $this->id)
                    ->where('role', $role->value);
            });
        })->orWhere(function ($query) use ($role) {
            // Access via groups
            $query->whereHas('accessGrants', function ($q) use ($role) {
                $q->whereIn('user_group_id', $this->userGroups->pluck('id'))
                    ->where('role', $role->value);
            });
        })->distinct();
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'requester_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function sessionAudits(): HasMany
    {
        return $this->hasMany(SessionAudit::class);
    }

    public function accessRestrictions(): HasMany
    {
        return $this->hasMany(UserAccessRestriction::class);
    }

    public function actionAudits(): HasMany
    {
        return $this->hasMany(ActionAudit::class);
    }
}
