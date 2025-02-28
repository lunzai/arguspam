<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetAccount extends Model
{
    /** @use HasFactory<\Database\Factories\AssetAccountFactory> */
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'name',
        'vault_path',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
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
