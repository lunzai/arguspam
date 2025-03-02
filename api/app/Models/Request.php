<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Enums\Status;
use App\Enums\RiskRating;
use App\Enums\RequestScope;

class Request extends Model
{
    /** @use HasFactory<\Database\Factories\RequestFactory> */
    use HasFactory;

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
        'status' => Status::class,
        'approver_risk_rating' => RiskRating::class,
        'ai_risk_rating' => RiskRating::class,
        'scope' => RequestScope::class,
    ];

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class);
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

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class);
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
