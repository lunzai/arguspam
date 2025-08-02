<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;

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
