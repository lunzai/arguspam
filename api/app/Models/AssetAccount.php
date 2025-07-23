<?php

namespace App\Models;

use App\Enums\AssetAccountType;
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
        'username',
        'password',
        'type',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'type' => AssetAccountType::class,
        'password' => 'encrypted',
        'username' => 'encrypted',
    ];

    public static $attributeLabels = [
        'asset_id' => 'Asset',
        'name' => 'Name',
        'username' => 'Username',
        'password' => 'Password',
        'type' => 'Type',
        'expires_at' => 'Expires At',
        'is_active' => 'Is Active',
    ];

    public static $includable = [
        'asset',
        'sessions',
        'createdBy',
        'updatedBy',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeJit($query)
    {
        return $query->where('type', AssetAccountType::JIT);
    }

    public function scopeAdmin($query)
    {
        return $query->where('type', AssetAccountType::ADMIN);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }
}
