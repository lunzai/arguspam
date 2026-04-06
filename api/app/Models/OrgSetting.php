<?php

namespace App\Models;

use Database\Factories\OrgSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgSetting extends Model
{
    /** @use HasFactory<OrgSettingFactory> */
    use HasFactory;

    protected $fillable = [
        'org_id',
        'key',
        'value',
    ];

    protected function casts(): array
    {
        return [
            'value' => 'array',
        ];
    }

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class);
    }
}
