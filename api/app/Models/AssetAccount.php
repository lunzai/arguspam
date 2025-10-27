<?php

namespace App\Models;

use App\Enums\AssetAccountType;
use App\Traits\HasBlamable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AssetAccount extends Model
{
    /** @use HasFactory<\Database\Factories\AssetAccountFactory> */
    use HasBlamable, HasFactory;

    protected $fillable = [
        'asset_id',
        'username',
        'password',
        'databases',
        'type',
        'ended_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'ended_at' => 'datetime',
        'expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'is_active' => 'boolean',
        'type' => AssetAccountType::class,
        'password' => 'encrypted',
        'username' => 'encrypted',
        'databases' => 'array',
    ];

    protected $hidden = [
        'password',
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
        'session',
        'createdBy',
        'updatedBy',
    ];

    #[Scope]
    public function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    public function expired(Builder $query): void
    {
        $query->where('expires_at', '<=', now());
    }

    #[Scope]
    public function jit(Builder $query): void
    {
        $query->where('type', AssetAccountType::JIT);
    }

    #[Scope]
    public function admin(Builder $query): void
    {
        $query->where('type', AssetAccountType::ADMIN);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isJit(): bool
    {
        return $this->type === AssetAccountType::JIT;
    }

    public function end(): void
    {
        $this->is_active = false;
        $this->ended_at = now();
        $this->save();
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function session(): HasOne
    {
        return $this->hasOne(Session::class, 'asset_account_id', 'id');
    }
}
