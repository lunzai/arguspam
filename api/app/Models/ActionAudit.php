<?php

namespace App\Models;

use App\Enums\AuditAction;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasExpandable;

class ActionAudit extends Model
{
    /** @use HasFactory<\Database\Factories\ActionAuditFactory> */
    use HasFactory, HasExpandable;

    protected $fillable = [
        'org_id',
        'user_id',
        'action_type',
        'entity_type',
        'entity_id',
        'description',
        'previous_state',
        'new_state',
        'ip_address',
        'user_agent',
        'additional_data',
    ];

    protected $casts = [
        'additional_data' => 'array',
        'previous_state' => 'array',
        'new_state' => 'array',
        'created_at' => 'datetime',
        'action_type' => AuditAction::class,
    ];

    protected $expandable = [
        'org',
        'user',
    ];

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
