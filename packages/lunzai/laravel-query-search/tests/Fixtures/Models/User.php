<?php

namespace Lunzai\QuerySearch\Tests\Fixtures\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Model
{
    protected $guarded = [];

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(UserSession::class);
    }
}
