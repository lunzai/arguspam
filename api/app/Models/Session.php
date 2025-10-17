<?php

namespace App\Models;

use App\Enums\RiskRating;
use App\Enums\SessionStatus;
use App\Events\SessionCancelled;
use App\Events\SessionCreated;
use App\Events\SessionEnded;
use App\Events\SessionExpired;
use App\Events\SessionStarted;
use App\Events\SessionTerminated;
use App\Events\SessionAiAudited;
use App\Services\OpenAI\OpenAiService;
use App\Traits\BelongsToOrganization;
use App\Traits\HasBlamable;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class Session extends Model implements ShouldHandleEventsAfterCommit
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
        'scheduled_start_datetime',
        'scheduled_end_datetime',
        'requested_duration',
        'actual_duration',
        'is_admin_account',
        'account_name',
        'ai_risk_rating',
        'ai_note',
        'ai_reviewed_at',
        'session_note',
        'status',
        'account_created_at',
        'account_revoked_at',
        'started_by',
        'started_at',
        'ended_at',
        'ended_by',
        'cancelled_by',
        'cancelled_at',
        'terminated_by',
        'terminated_at',
        'expired_at',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'scheduled_start_datetime' => 'datetime',
        'scheduled_end_datetime' => 'datetime',
        'requested_duration' => 'integer',
        'actual_duration' => 'integer',
        'is_admin_account' => 'boolean',
        'ai_risk_rating' => RiskRating::class,
        'ai_reviewed_at' => 'datetime',
        'status' => SessionStatus::class,
        'account_created_at' => 'datetime',
        'account_revoked_at' => 'datetime',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'terminated_at' => 'datetime',
        'expired_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public static $includable = [
        'org',
        'request',
        'asset',
        'assetAccount',
        'requester',
        'approver',
        'startedBy',
        'endedBy',
        'cancelledBy',
        'terminatedBy',
        'flags',
        'audits',
        'createdBy',
        'updatedBy',
    ];

    protected $dispatchesEvents = [
        'created' => SessionCreated::class,
    ];

    public static function createFromRequest(Request $request): self
    {
        $session = self::create([
            'org_id' => $request->org_id,
            'request_id' => $request->id,
            'asset_id' => $request->asset_id,
            'asset_account_id' => $request->asset_account_id,
            'requester_id' => $request->requester_id,
            'approver_id' => $request->approved_by,
            'scheduled_start_datetime' => $request->start_datetime,
            'scheduled_end_datetime' => $request->end_datetime,
            'requested_duration' => $request->duration,
            'status' => SessionStatus::SCHEDULED,
            'is_admin_account' => $request->asset_account_id ? false : true,
            'created_by' => $request->approved_by,
        ]);
        return $session;
    }

    public function requestedDurationForHumans(): Attribute
    {
        return Attribute::make(
            get: function () {
                return CarbonInterval::minutes($this->requested_duration)
                    ->cascade()
                    ->forHumans();
            },
        );
    }

    public function actualDurationForHumans(): Attribute
    {
        return Attribute::make(
            get: function () {
                return CarbonInterval::minutes($this->actual_duration)
                    ->cascade()
                    ->forHumans();
            },
        );
    }

    public function getAiAudit(OpenAiService $openAiService): void
    {
        $evaluation = $openAiService->reviewSession($this);
        $this->ai_note = $evaluation['output']['ai_note'];
        $this->ai_risk_rating = $evaluation['output']['ai_risk_rating'];
        $this->ai_reviewed_at = now();
        SessionAiAudited::dispatchIf($this->save(), $this);
    }

    public function canStart(): bool
    {
        return $this->status == SessionStatus::SCHEDULED &&
            now()->between($this->scheduled_start_datetime, $this->scheduled_end_datetime);
    }

    public function canCancel(): bool
    {
        return $this->status == SessionStatus::SCHEDULED &&
            $this->isBeforeScheduledEndDatetime();
    }

    public function canTerminate(): bool
    {
        return $this->status == SessionStatus::STARTED;
    }

    public function canEnd(): bool
    {
        return $this->status == SessionStatus::STARTED;
    }

    public function canExpire(): bool
    {
        return $this->status == SessionStatus::SCHEDULED &&
            $this->isAfterScheduledEndDatetime();
    }

    public function isActive(): bool
    {
        return $this->status == SessionStatus::STARTED;
    }

    public function canBeStarted(): bool
    {
        return $this->canStart();
    }

    public function isAfterScheduledStartDatetime(): bool
    {
        return $this->scheduled_start_datetime->isAfter(now());
    }

    public function isBeforeScheduledEndDatetime(): bool
    {
        return now()->isBefore($this->scheduled_end_datetime);
    }

    public function isAfterScheduledEndDatetime(): bool
    {
        return now()->isAfter($this->scheduled_end_datetime);
    }

    public function isRequiredManualReview(): bool
    {
        if (!$this->ai_risk_rating) {
            return false;
        }
        return $this->ai_risk_rating == RiskRating::HIGH ||
            $this->ai_risk_rating == RiskRating::CRITICAL;
    }

    public function start(): void
    {
        if (!$this->canStart()) {
            throw new \Exception('Session is not eligible for starting');
        }
        $this->status = SessionStatus::STARTED;
        $this->started_at = now();
        $this->started_by = Auth::id();
        // $this->save();
        SessionStarted::dispatchIf($this->save(), $this);
    }

    public function end(): void
    {
        if (!$this->canEnd()) {
            throw new \Exception('Session is not eligible for ending');
        }
        $this->status = SessionStatus::ENDED;
        $this->ended_at = now();
        $this->ended_by = Auth::id();
        SessionEnded::dispatchIf($this->save(), $this);
    }

    public function cancel(): void
    {
        if (!$this->canCancel()) {
            throw new \Exception('Session is not eligible for cancellation');
        }
        $this->status = SessionStatus::CANCELLED;
        $this->cancelled_at = now();
        $this->cancelled_by = Auth::id();
        SessionCancelled::dispatchIf($this->save(), $this);
    }

    public function terminate(): void
    {
        if (!$this->canTerminate()) {
            throw new \Exception('Session is not eligible for termination');
        }
        $this->status = SessionStatus::TERMINATED;
        $this->terminated_at = now();
        $this->terminated_by = Auth::id();
        SessionTerminated::dispatchIf($this->save(), $this);
    }

    public function expire(): void
    {
        if (!$this->canExpire()) {
            throw new \Exception('Session is not eligible for expiration');
        }
        $this->status = SessionStatus::EXPIRED;
        $this->expired_at = now();
        SessionExpired::dispatchIf($this->save(), $this);
    }

    #[Scope]
    protected function scheduled(Builder $query): Builder
    {
        return $query->where('status', SessionStatus::SCHEDULED->value);
    }

    #[Scope]
    protected function scheduledEndDateNowOrPast(Builder $query): Builder
    {
        return $query->where('scheduled_end_datetime', '<=', now());
    }

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class);
    }

    public function request(): belongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function assetAccount(): HasOne
    {
        return $this->hasOne(AssetAccount::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    public function startedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'started_by');
    }

    public function endedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ended_by');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function terminatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'terminated_by');
    }

    public function flags(): HasMany
    {
        return $this->hasMany(SessionFlag::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(SessionAudit::class);
    }
}
