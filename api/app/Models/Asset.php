<?php

namespace App\Models;

use App\Enums\AssetAccessRole;
use App\Enums\Dbms;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasExpandable;

class Asset extends Model
{
    /** @use HasFactory<\Database\Factories\AssetFactory> */
    use HasFactory, HasExpandable;

    use SoftDeletes;

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
        'deleted_at' => 'datetime',
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

    protected $expandable = [
        'org',
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

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class);
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
}
