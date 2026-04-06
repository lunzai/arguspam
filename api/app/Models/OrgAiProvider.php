<?php

namespace App\Models;

use Database\Factories\OrgAiProviderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgAiProvider extends Model
{
    /** @use HasFactory<OrgAiProviderFactory> */
    use HasFactory;

    protected $fillable = [
        'org_id',
        'sort_order',
        'driver',
        'label',
        'options',
        'api_key',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'is_enabled' => 'boolean',
            'api_key' => 'encrypted',
        ];
    }

    protected $hidden = [
        'api_key',
    ];

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class);
    }

    public function resolveRouteBinding($value, $field = null): ?static
    {
        $orgId = (int) request()->get(config('pam.org.request_attribute'));
        if (!$orgId) {
            return null;
        }

        return static::query()
            ->where('org_id', $orgId)
            ->where($field ?? $this->getRouteKeyName(), $value)
            ->first();
    }

    public static function configNameFor(int $orgId, int $providerId): string
    {
        return "org_{$orgId}_aiprov_{$providerId}";
    }
}
