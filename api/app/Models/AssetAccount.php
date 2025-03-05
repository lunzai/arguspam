<?php

namespace App\Models;

use App\Traits\HasBlamable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetAccount extends Model
{
    /** @use HasFactory<\Database\Factories\AssetAccountFactory> */
    use HasBlamable, HasFactory;

    protected $fillable = [
        'asset_id',
        'name',
        'vault_path',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public static $attributeLabels = [
        'asset_id' => 'Asset',
        'name' => 'Name',
        'vault_path' => 'Vault Path',
        'is_default' => 'Is Default',
    ];

    public static $includable = [
        'asset',
        'createdBy',
        'updatedBy',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }
}
