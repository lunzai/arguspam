<?php

namespace App\Models;

use App\Enums\RequestScope;
use App\Enums\RequestStatus;
use App\Enums\RiskRating;
use App\Events\RequestApproved;
use App\Events\RequestCreated;
use App\Events\RequestRejected;
use App\Events\RequestSubmitted;
use App\Services\OpenAI\OpenAiService;
use App\Traits\BelongsToOrganization;
use App\Traits\HasBlamable;
use Carbon\CarbonInterval;
use Illuminate\Contracts\Events\ShouldHandleEventsAfterCommit;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Request extends Model implements ShouldHandleEventsAfterCommit
{
    /** @use HasFactory<\Database\Factories\RequestFactory> */
    use BelongsToOrganization, HasBlamable, HasFactory;

    protected $fillable = [
        'org_id',
        'asset_id',
        'asset_account_id',
        'requester_id',
        'start_datetime',
        'end_datetime',
        'duration',
        'reason',
        'intended_query',
        'scope',
        'is_access_sensitive_data',
        'sensitive_data_note',
        'approver_note',
        'approver_risk_rating',
        'ai_note',
        'ai_risk_rating',
        'status',
        'approved_by',
        'approved_at',
        'rejected_by',
        'rejected_at',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'is_access_sensitive_data' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'status' => RequestStatus::class,
        'approver_risk_rating' => RiskRating::class,
        'ai_risk_rating' => RiskRating::class,
        'scope' => RequestScope::class,
    ];

    public static $attributeLabels = [
        'org_id' => 'Organization',
        'asset_id' => 'Asset',
        'asset_account_id' => 'Account',
        'requester_id' => 'Requester',
        'start_datetime' => 'Start',
        'end_datetime' => 'End',
        'duration' => 'Duration',
        'reason' => 'Reason',
        'intended_query' => 'Intended Query',
        'scope' => 'Scope',
        'is_access_sensitive_data' => 'Is Access Sensitive Data',
        'sensitive_data_note' => 'Sensitive Data Note',
        'approver_note' => 'Approver Note',
        'approver_risk_rating' => 'Approver Risk Rating',
        'ai_note' => 'AI Note',
        'ai_risk_rating' => 'AI Risk Rating',
        'status' => 'Status',
        'approved_by' => 'Approved By',
        'approved_at' => 'Approved At',
        'rejected_by' => 'Rejected By',
        'rejected_at' => 'Rejected At',
    ];

    public static $includable = [
        'org',
        'asset',
        'assetAccount',
        'requester',
        'approver',
        'session',
        'audits',
        'createdBy',
        'updatedBy',
    ];

    protected $dispatchesEvents = [
        'created' => RequestCreated::class,
    ];

    public function durationForHumans(): Attribute
    {
        return Attribute::make(
            get: function () {
                return CarbonInterval::minutes($this->duration)
                    ->cascade()
                    ->forHumans();
            },
        );
    }

    public function getAiEvaluation(OpenAiService $openAiService): void
    {
        $evaluation = $openAiService->evaluateAccessRequest($this);

        $this->ai_note = $evaluation['output']['ai_note'];
        $this->ai_risk_rating = $evaluation['output']['ai_risk_rating'];
        $this->save();
    }

    public function submit(): void
    {
        $this->status = RequestStatus::SUBMITTED;
        RequestSubmitted::dispatchIf($this->save(), $this);
    }

    public function reject(): void
    {
        $this->status = RequestStatus::REJECTED;
        RequestRejected::dispatchIf($this->save(), $this);
    }

    public function approve(): void
    {
        $this->status = RequestStatus::APPROVED;
        RequestApproved::dispatchIf($this->save(), $this);
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
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function rejecter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function session(): HasOne
    {
        return $this->hasOne(Session::class);
    }

    public function audits(): HasMany
    {
        return $this->hasMany(SessionAudit::class);
    }
}
