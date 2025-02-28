<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    /** @use HasFactory<\Database\Factories\SessionFactory> */
    use HasFactory;

    protected $fillable = [
        'org_id',
        'request_id',
        'asset_id',
        'requester_id',
        'start_datetime',
        'end_datetime',
        'scheduled_end_datetime',
        'requested_duration',
        'actual_duration',
        'is_jit',
        'account_name',
        'jit_vault_path',
        'session_note',
        'is_expired',
        'is_terminated',
        'is_checkin',
        'status',
        'checkin_by',
        'checkin_at',
        'terminated_by',
        'terminated_at',
        'ended_at',
        'ended_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'scheduled_end_datetime' => 'datetime',
        'is_jit' => 'boolean',
        'is_expired' => 'boolean',
        'is_terminated' => 'boolean',
        'is_checkin' => 'boolean',
    ];

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class);
    }

    public function request(): BelongsTo
    {
        return $this->BelongsTo(Request::class);
    }

    public function asset(): HasOne
    {
        return $this->hasOne(Asset::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function checkinBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function terminatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function endedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(SessionAudit::class);
    }
}
