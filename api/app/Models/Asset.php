<?php

namespace App\Models;

use App\Enums\AssetAccessRole;
use App\Enums\Dbms;
use App\Enums\Status;
use App\Traits\BelongsToOrganization;
use App\Traits\HasBlamable;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Asset extends Model
{
    /** @use HasFactory<\Database\Factories\AssetFactory> */
    use BelongsToOrganization, HasBlamable, HasFactory, HasStatus, SoftDeletes;

    protected $fillable = [
        'org_id',
        'name',
        'description',
        'status',
        'host',
        'port',
        'dbms',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'dbms' => Dbms::class,
        'status' => Status::class,
    ];

    public static $attributeLabels = [
        'org_id' => 'Organization',
        'name' => 'Name',
        'description' => 'Description',
        'status' => 'Status',
        'host' => 'Host',
        'port' => 'Port',
        'dbms' => 'DBMS',
    ];

    public static $includable = [
        'org',
        'adminAccount',
        'accounts',
        'accessGrants',
        'requests',
        'sessions',
        'users',
        'userGroups',
        'approverUserGroups',
        'requesterUserGroups',
        'approverUsers',
        'requesterUsers',
        'createdBy',
        'updatedBy',
    ];

    protected static function booted(): void
    {
        static::softDeleted(function (Asset $asset) {
            $adminAccount = $asset->adminAccount()->first();
            if ($adminAccount) {
                $adminAccount->delete();
            }
        });
    }

    public function createAccount(Session $session): AssetAccount
    {
        return new AssetAccount;
    }

    public function adminAccount(): HasOne
    {
        return $this->hasOne(AssetAccount::class)
            ->active()
            ->admin();
    }

    public function jitAccount(): HasMany
    {
        return $this->accounts()->jit();
    }

    public function accounts(): HasMany
    {
        return $this->hasMany(AssetAccount::class);
    }

    public function accessGrants(): HasMany
    {
        return $this->hasMany(AssetAccessGrant::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'asset_access_grants');
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'asset_access_grants');
    }

    public function approverUserGroups(): BelongsToMany
    {
        return $this->userGroups()
            ->wherePivot('role', AssetAccessRole::APPROVER->value);
    }

    public function requesterUserGroups(): BelongsToMany
    {
        return $this->userGroups()
            ->wherePivot('role', AssetAccessRole::REQUESTER->value);
    }

    public function approverUsers(): BelongsToMany
    {
        return $this->users()
            ->wherePivot('role', AssetAccessRole::APPROVER->value);
    }

    public function requesterUsers(): BelongsToMany
    {
        return $this->users()
            ->wherePivot('role', AssetAccessRole::REQUESTER->value);
    }

    /**
     * Get all approvers for this asset (direct users + users from approver groups)
     *
     * @return \Illuminate\Support\Collection
     */
    public function getApprovers()
    {
        // Get direct approver users
        $directApprovers = $this->approverUsers;

        // Get approvers from user groups
        $approverUserGroups = $this->approverUserGroups;
        $groupApprovers = collect();

        foreach ($approverUserGroups as $userGroup) {
            $groupApprovers = $groupApprovers->merge($userGroup->users);
        }

        // Combine and deduplicate approvers by ID
        return $directApprovers->merge($groupApprovers)->unique('id');
    }
}
