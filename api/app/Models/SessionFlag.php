<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\SessionFlag as SessionFlagEnum;

class SessionFlag extends Model
{
    protected $fillable = [
        'session_id',
        'flag',
    ];

    protected $casts = [
        'flag' => SessionFlagEnum::class,
        'created_at' => 'datetime',
    ];

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }
}
