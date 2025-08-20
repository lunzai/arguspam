<?php

namespace App\Models;

use App\Enums\SessionStatus;
use App\Events\SessionEnded;
use App\Events\SessionStarted;
use App\Traits\BelongsToOrganization;
use App\Traits\HasBlamable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Session extends Model
{
    /** @use HasFactory<\Database\Factories\SessionFactory> */
    use BelongsToOrganization, HasBlamable, HasFactory;

    protected $fillable = [
        'org_id',
        'request_id',
        'asset_id',
        'asset_account_id',
        'requester_id',
        'approver_id',
        'start_datetime',
        'end_datetime',
        'scheduled_end_datetime',
        'requested_duration',
        'actual_duration',
        'is_admin',
        'account_name',
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
        'is_admin' => 'boolean',
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
        'asset_account_id' => 'Asset Account',
        'requester_id' => 'Requester',
        'start_datetime' => 'Start',
        'end_datetime' => 'End',
        'scheduled_end_datetime' => 'Scheduled End',
        'requested_duration' => 'Requested Duration',
        'actual_duration' => 'Actual Duration',
        'is_admin' => 'Is Admin',
        'account_name' => 'Account',
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
        'assetAccount',
        'requester',
        'checkinBy',
        'terminatedBy',
        'endedBy',
        'audits',
        'createdBy',
        'updatedBy',
    ];

    protected static function booted()
    {
        static::updated(function (Session $session) {
            if (!$session->isDirty('status')) {
                return;
            }
            if ($session->status == SessionStatus::ACTIVE && $session->getOriginal('status') == SessionStatus::SCHEDULED) {
                event(new SessionStarted($session, []));
            } elseif (in_array($session->status, [SessionStatus::TERMINATED, SessionStatus::ENDED]) &&
                in_array($session->getOriginal('status'), [SessionStatus::ACTIVE, SessionStatus::SCHEDULED])) {
                event(new SessionEnded($session, []));
            }
        });
    }

    public function isActive(): bool
    {
        return $this->status == SessionStatus::ACTIVE &&
            now()->between($this->start_datetime, $this->end_datetime);
    }

    public function canBeStarted(): bool
    {
        return $this->status == SessionStatus::SCHEDULED &&
            now()->between($this->start_datetime, $this->scheduled_end_datetime);
    }

    public function getRemainingDuration(): int
    {
        if (!$this->isActive()) {
            return 0;
        }
        return now()->diffInSeconds($this->end_datetime);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assetAccount(): BelongsTo
    {
        return $this->belongsTo(AssetAccount::class);
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
