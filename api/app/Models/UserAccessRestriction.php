<?php

namespace App\Models;

use App\Enums\RestrictionType;
use App\Enums\Status;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasExpandable;

class UserAccessRestriction extends Model
{
    /** @use HasFactory<\Database\Factories\UserAccessRestrictionFactory> */
    use HasFactory, HasExpandable;

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

    protected $expandable = [
        'user',
        'createdBy',
        'updatedBy',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
