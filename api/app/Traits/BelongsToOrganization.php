<?php

namespace App\Traits;

use App\Models\Org;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

trait BelongsToOrganization
{
    /**
     * Boot the trait and add global scopes and event listeners.
     */
    protected static function bootBelongsToOrganization()
    {
        static::addGlobalScope('organization', function (Builder $builder) {
            $orgId = self::getCurrentOrganizationId();
            if ($orgId) {
                $builder->where('org_id', $orgId);
            }
        });
        static::creating(function ($model) {
            if (!$model->org_id) {
                $orgId = self::getCurrentOrganizationId();
                if ($orgId) {
                    $model->org_id = $orgId;
                }
            }
        });
    }

    /**
     * Get the organization relationship.
     */
    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class);
    }

    /**
     * Scope query to a specific organization.
     */
    public function scopeForOrganization(Builder $query, $orgId): Builder
    {
        return $query->where('org_id', $orgId);
    }

    /**
     * Scope query to exclude organization filtering (useful for admin operations).
     */
    public function scopeWithoutOrganizationScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('organization');
    }

    /**
     * Check if the model belongs to a specific organization.
     */
    public function isInOrganization($orgId): bool
    {
        return $this->org_id == $orgId;
    }

    /**
     * Check if the model belongs to the current organization.
     */
    public function isInCurrentOrganization(): bool
    {
        $currentOrgId = self::getCurrentOrganizationId();
        return $currentOrgId && $this->org_id == $currentOrgId;
    }

    /**
     * Get the current organization ID from the request.
     */
    protected static function getCurrentOrganizationId()
    {
        $request = request();
        if (!$request) {
            return null;
        }
        // Get from request attribute (set by middleware)
        $orgId = $request->get(config('pam.org.request_attribute'));
        if ($orgId) {
            return $orgId;
        }
        // Fallback to header (for cases where middleware might not have run)
        return $request->header(config('pam.org.request_header'));
    }

    /**
     * Get the current organization model.
     */
    public static function getCurrentOrganization(): ?Org
    {
        $orgId = self::getCurrentOrganizationId();
        return $orgId ? Org::find($orgId) : null;
    }

    /**
     * Create a new model instance for a specific organization.
     */
    public static function createForOrganization(array $attributes, $orgId = null)
    {
        if (!$orgId) {
            $orgId = self::getCurrentOrganizationId();
        }
        if (!$orgId) {
            throw new \InvalidArgumentException('Organization ID is required');
        }
        $attributes['org_id'] = $orgId;
        return static::create($attributes);
    }

    /**
     * Get all records for the current organization.
     */
    public static function forCurrentOrganization(): Builder
    {
        $orgId = self::getCurrentOrganizationId();
        if (!$orgId) {
            throw new \InvalidArgumentException('No current organization context available');
        }
        return static::forOrganization($orgId);
    }

    /**
     * Check if the current request has organization context.
     */
    public static function hasOrganizationContext(): bool
    {
        return self::getCurrentOrganizationId() !== null;
    }
}
