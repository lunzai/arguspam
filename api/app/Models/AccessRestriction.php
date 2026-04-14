<?php

namespace App\Models;

use App\Enums\AccessRestrictionType;
use App\Enums\Status;
use App\Traits\HasBlamable;
use App\Traits\HasStatus;
use Database\Factories\AccessRestrictionFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AccessRestriction extends Model
{
    /** @use HasFactory<AccessRestrictionFactory> */
    use HasBlamable, HasFactory, HasStatus;

    protected $fillable = [
        'name',
        'description',
        'type',
        'data',
        'status',
        'weight',
    ];

    protected $casts = [
        'type' => AccessRestrictionType::class,
        'data' => 'array',
        'status' => Status::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static $includable = [
        'users',
        'userGroups',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class);
    }
}
