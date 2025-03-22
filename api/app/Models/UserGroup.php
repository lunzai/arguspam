<?php

namespace App\Models;

use App\Enums\AssetAccessRole;
use App\Enums\Status;
use App\Traits\HasBlamable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserGroup extends Model
{
    /** @use HasFactory<\Database\Factories\UserGroupFactory> */
    use HasBlamable, HasFactory;

    protected $fillable = [
        'org_id',
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => Status::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public static $attributeLabels = [
        'org_id' => 'Organization',
        'name' => 'Name',
        'description' => 'Description',
        'status' => 'Status',
    ];

    public static $includable = [
        'users',
        'assetAccessGrants',
        'org',
        'createdBy',
        'updatedBy',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function assetAccessGrants(): HasMany
    {
        return $this->hasMany(AssetAccessGrant::class);
    }

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

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class);
    }
}
