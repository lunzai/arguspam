<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Traits\HasExpandable;

class AssetAccount extends Model
{
    /** @use HasFactory<\Database\Factories\AssetAccountFactory> */
    use HasFactory, HasExpandable;

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

    protected $expandable = [
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
