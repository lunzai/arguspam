<?php

namespace App\Models;

use App\Enums\AssetAccessRole;
use App\Traits\HasBlamable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetAccessGrant extends Model
{
    /** @use HasFactory<\Database\Factories\AssetAccessGrantFactory> */
    use HasBlamable, HasFactory;

    protected $fillable = [
        'asset_id',
        'user_id',
        'user_group_id',
        'role',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'role' => AssetAccessRole::class,
    ];

    public static $includable = [
        'asset',
        'user',
        'userGroup',
        'createdBy',
        'updatedBy',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function userGroup(): BelongsTo
    {
        return $this->belongsTo(UserGroup::class);
    }
}
