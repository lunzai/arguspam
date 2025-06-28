<?php

namespace App\Models;

use App\Enums\SessionStatus;
use App\Traits\HasBlamable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Session extends Model
{
    /** @use HasFactory<\Database\Factories\SessionFactory> */
    use HasBlamable, HasFactory;

    protected $fillable = [
        // 'org_id',
        // 'request_id',
        // 'asset_id',
        // 'requester_id',
        // 'start_datetime',
        // 'end_datetime',
        // 'scheduled_end_datetime',
        // 'requested_duration',
        // 'actual_duration',
        // 'is_jit',
        // 'account_name',
        // 'jit_vault_path',
        'session_note',
        // 'is_expired',
        // 'is_terminated',
        // 'is_checkin',
        // 'status',
        // 'checkin_by',
        // 'checkin_at',
        // 'terminated_by',
        // 'terminated_at',
        // 'ended_at',
        // 'ended_by',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'scheduled_end_datetime' => 'datetime',
        'is_jit' => 'boolean',
        'is_expired' => 'boolean',
        'is_terminated' => 'boolean',
        'is_checkin' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'checkin_at' => 'datetime',
        'terminated_at' => 'datetime',
        'ended_at' => 'datetime',
        'status' => SessionStatus::class,
    ];

    public static $attributeLabels = [
        'org_id' => 'Organization',
        'request_id' => 'Request',
        'asset_id' => 'Asset',
        'requester_id' => 'Requester',
        'start_datetime' => 'Start',
        'end_datetime' => 'End',
        'scheduled_end_datetime' => 'Scheduled End',
        'requested_duration' => 'Requested Duration',
        'actual_duration' => 'Actual Duration',
        'is_jit' => 'Is JIT',
        'account_name' => 'Account',
        'jit_vault_path' => 'JIT Vault Path',
        'session_note' => 'Session Note',
        'is_expired' => 'Is Expired',
        'is_terminated' => 'Is Terminated',
        'is_checkin' => 'Is Checkin',
        'status' => 'Status',
        'checkin_by' => 'Checkin By',
        'checkin_at' => 'Checkin At',
        'terminated_by' => 'Terminated By',
        'terminated_at' => 'Terminated At',
        'ended_at' => 'Ended At',
        'ended_by' => 'Ended By',
    ];

    public static $includable = [
        'org',
        'request',
        'asset',
        'requester',
        'checkinBy',
        'terminatedBy',
        'endedBy',
        'audits',
        'createdBy',
        'updatedBy',
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
