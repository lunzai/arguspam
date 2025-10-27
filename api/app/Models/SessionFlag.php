<?php

namespace App\Models;

use App\Enums\SessionFlag as SessionFlagEnum;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SessionFlag extends Model
{
    public $timestamps = false;

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
