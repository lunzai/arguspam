<?php

namespace App\Models;

use App\Enums\RestrictionType;
use App\Enums\Status;
use App\Traits\HasBlamable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAccessRestriction extends Model
{
    /** @use HasFactory<\Database\Factories\UserAccessRestrictionFactory> */
    use HasBlamable, HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'value',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'status' => Status::class,
        'type' => RestrictionType::class,
        'value' => 'array',
    ];

    public static $attributeLabels = [
        'user_id' => 'User',
        'type' => 'Type',
        'value' => 'Value',
        'status' => 'Status',
    ];

    public static $includable = [
        'user',
        'createdBy',
        'updatedBy',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
