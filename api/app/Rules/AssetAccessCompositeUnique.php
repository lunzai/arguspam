<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

/**
 * Validation rule to ensure unique combination of user/user_group, asset, and role.
 *
 * This rule validates that a specific user or user group doesn't already have
 * the same role assigned to the same asset, ensuring no duplicate access grants.
 */
class AssetAccessCompositeUnique implements ValidationRule
{
    protected $table;
    protected $column;
    protected $role;
    protected $assetId;
    protected $ignoreId;

    public function __construct(string $table, string $column, string $role, string $assetId, ?string $ignoreId = null)
    {
        $this->table = $table;
        $this->column = $column;
        $this->role = $role;
        $this->assetId = $assetId;
        $this->ignoreId = $ignoreId;
    }

    /**
     * Validate the given attribute value.
     *
     * @param  string  $attribute  The attribute name being validated
     * @param  mixed  $value  The value being validated
     * @param  Closure  $fail  The failure callback
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->role || !$this->assetId) {
            return;
        }
        $query = DB::table($this->table)
            ->where($this->column, $value)
            ->where('asset_id', $this->assetId)
            ->where('role', $this->role);
        if ($this->ignoreId) {
            $query->where('id', '!=', $this->ignoreId);
        }
        if ($query->exists()) {
            $entityType = $this->column === 'user_id' ? 'user' : 'user group';
            $fail("This {$entityType} already has {$this->role} role assigned to this asset.");
        }
    }
}
