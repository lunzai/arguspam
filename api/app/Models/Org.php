<?php

namespace App\Models;

use App\Enums\Status;
use App\Traits\HasBlamable;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use App\Policies\OrgPolicy;

#[UsePolicy(OrgPolicy::class)]
class Org extends Model
{
    /** @use HasFactory<\Database\Factories\OrgFactory> */
    use HasBlamable, HasFactory, HasStatus, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => Status::class,
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public static $attributeLabels = [
        'name' => 'Name',
        'description' => 'Description',
        'status' => 'Status',
    ];

    public static $includable = [
        'users',
        'userGroups',
        'assets',
        'requests',
        'sessions',
        'sessionAudits',
        'actionAudits',
        'createdBy',
        'updatedBy',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function userGroups(): HasMany
    {
        return $this->hasMany(UserGroup::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function sessionAudits(): HasMany
    {
        return $this->hasMany(SessionAudit::class);
    }

    public function actionAudits(): HasMany
    {
        return $this->hasMany(ActionAudit::class);
    }
}
