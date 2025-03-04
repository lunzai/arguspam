<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasExpandable;

class SessionAudit extends Model
{
    /** @use HasFactory<\Database\Factories\SessionAuditFactory> */
    use HasFactory, HasExpandable;

    protected $fillable = [
        'org_id',
        'session_id',
        'request_id',
        'asset_id',
        'user_id',
        'query_text',
        'query_timestamp',
    ];

    protected $casts = [
        'query_timestamp' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $expandable = [
        'org',
        'session',
        'request',
        'asset',
        'user',
    ];

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
