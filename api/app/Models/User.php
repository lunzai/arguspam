<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\AssetAccessRole;
use App\Enums\Status;
use App\Http\Filters\QueryFilter;
use App\Traits\HasBlamable;
use App\Traits\HasRbac;
use App\Traits\HasStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use PragmaRX\Google2FAQRCode\Google2FA;
use DateTimeZone;
use DateTime;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasBlamable, HasFactory,
        HasRbac, HasStatus, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'status',
        'default_timezone',
    ];

    protected $guarded = [
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'deleted_at',
        'deleted_by',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    public static $includable = [
        'orgs',
        'userGroups',
        'assetAccessGrants',
        'approverAssetAccessGrants',
        'requesterAssetAccessGrants',
        'requests',
        'sessions',
        'accessRestrictions',
        'roles',
        'permissions',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'two_factor_enabled' => 'boolean',
            'two_factor_enabled_at' => 'datetime',
            'two_factor_confirmed_at' => 'datetime',
            'two_factor_secret' => 'encrypted',
            'password' => 'hashed',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
            'deleted_at' => 'datetime',
            'last_login_at' => 'datetime',
            'status' => Status::class,
        ];
    }

    public static $attributeLabels = [
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'status' => 'Status',
        'two_factor_enabled' => 'MFA',
        'last_login_at' => 'Last Login At',
        'default_timezone' => 'Timezone',
    ];

    public function getTimezone() : DateTimeZone
    {
        return new DateTimeZone(
            $this->default_timezone ?: 
            config('pam.user.default_timezone', 'UTC')
        );
    }

    public function timezone(): Attribute
    {
        return Attribute::make(
            get: function () {
                $tz = $this->getTimezone();
                $dt = new DateTime('now', $tz);
                $offset = $dt->format('P');
                $tzName = $tz->getName();
                if ($offset === '+00:00') {
                    return 'UTC';
                }
                if (preg_match('/^[+-]/', $tzName)) {
                    return 'GMT' . $offset;
                }
                return 'GMT' . $offset . ' ' . $tzName;
            },
        );
    }

    public function twoFactorQrCode(): Attribute
    {
        $appName = config('app.name');
        if (config('app.env') !== 'production' && config('app.env') !== 'prod') {
            $appName = $appName.' ('.config('app.env').')';
        }
        return Attribute::make(
            get: function () use ($appName) {
                if (!$this->twoFactorPendingConfirmation) {
                    return null;
                }
                $inlineQr = (new Google2FA)->getQRCodeInline(
                    $appName,
                    $this->email,
                    $this->two_factor_secret,
                );
                return 'data:image/svg+xml;base64,'.base64_encode($inlineQr);
            }
        );
    }

    public function twoFactorPendingConfirmation(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->isTwoFactorPendingConfirmation(),
        );
    }

    public function verifyTwoFactorCode(string $code): bool
    {
        if (config('pam.auth.bypass_2fa')) {
            return true;
        }
        return (new Google2FA)->verifyKey($this->two_factor_secret, $code);
    }

    public function isTwoFactorEnabled(): bool
    {
        return $this->two_factor_enabled;
    }

    public function isTwoFactorConfirmed(): bool
    {
        return $this->two_factor_confirmed_at !== null;
    }

    public function isTwoFactorPendingConfirmation(): bool
    {
        return $this->isTwoFactorEnabled() && !$this->isTwoFactorConfirmed();
    }

    public function generateTwoFactorSecret(): string
    {
        return (new Google2FA)->generateSecretKey();
    }

    public function inOrg(Org $org): bool
    {
        return $this->orgs->contains($org);
    }

    public function scopeFilter(Builder $builder, QueryFilter $filter)
    {
        return $filter->apply($builder);
    }

    public function orgs(): BelongsToMany
    {
        return $this->belongsToMany(Org::class);
    }

    public function userGroups(): BelongsToMany
    {
        return $this->belongsToMany(UserGroup::class, 'user_user_group');
    }

    public function assetAccessGrants(): HasMany
    {
        return $this->hasMany(AssetAccessGrant::class);
    }

    // public function approverAssetAccessGrants(): HasMany
    // {
    //     return $this->assetAccessGrants()
    //         ->where('role', AssetAccessRole::APPROVER->value);
    // }

    // public function requesterAssetAccessGrants(): HasMany
    // {
    //     return $this->assetAccessGrants()
    //         ->where('role', AssetAccessRole::REQUESTER->value);
    // }

    public function requesterAssets(): BelongsToMany
    {
        return $this->belongsToMany(
            Asset::class,
            'asset_access_grants',
        )->where('asset_access_grants.role', AssetAccessRole::REQUESTER->value);
    }

    public function approverAssets(): BelongsToMany
    {
        return $this->belongsToMany(
            Asset::class,
            'asset_access_grants',
        )->where('asset_access_grants.role', AssetAccessRole::APPROVER->value);
    }

    public function allRequesterAssets(): Builder
    {
        return $this->getAssetByRole(AssetAccessRole::REQUESTER);
    }

    public function allApproverAssets(): Builder
    {
        return $this->getAssetByRole(AssetAccessRole::APPROVER);
    }

    public function allAssets(): Builder
    {
        return Asset::where(function ($query) {
            // Direct user access
            $query->whereHas('accessGrants', function ($q) {
                $q->where('user_id', $this->id);
            });
        })->orWhere(function ($query) {
            // Access via groups
            $query->whereHas('accessGrants', function ($q) {
                $q->whereIn('user_group_id', $this->userGroups->pluck('id'));
            });
        })->distinct();
    }

    private function getAssetByRole(AssetAccessRole $role): Builder
    {
        return Asset::where(function ($query) use ($role) {
            // Direct user access
            $query->whereHas('accessGrants', function ($q) use ($role) {
                $q->where('user_id', $this->id)
                    ->where('role', $role->value);
            });
        })->orWhere(function ($query) use ($role) {
            // Access via groups
            $query->whereHas('accessGrants', function ($q) use ($role) {
                $q->whereIn('user_group_id', $this->userGroups->pluck('id'))
                    ->where('role', $role->value);
            });
        })->distinct();
    }

    public function requests(): HasMany
    {
        return $this->hasMany(Request::class, 'requester_id');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function sessionAudits(): HasMany
    {
        return $this->hasMany(SessionAudit::class);
    }

    public function accessRestrictions(): HasMany
    {
        return $this->hasMany(UserAccessRestriction::class);
    }

    public function actionAudits(): HasMany
    {
        return $this->hasMany(ActionAudit::class);
    }
}
