<?php

namespace App\Models;

use App\Enums\AiAgentRole;
use Database\Factories\OrgAiAgentFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgAiAgent extends Model
{
    /** @use HasFactory<OrgAiAgentFactory> */
    use HasFactory;

    protected $fillable = [
        'org_id',
        'role',
        'failover',
        'temperature',
        'max_output_tokens',
        'request_timeout_seconds',
        'provider_metadata',
    ];

    protected function casts(): array
    {
        return [
            'role' => AiAgentRole::class,
            'failover' => 'array',
            'temperature' => 'float',
            'provider_metadata' => 'array',
        ];
    }

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
}
