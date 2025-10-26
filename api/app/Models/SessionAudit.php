<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\MassPrunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class SessionAudit extends Model
{
    /** @use HasFactory<\Database\Factories\SessionAuditFactory> */
    use HasFactory, MassPrunable;

    public $timestamps = false;

    protected $fillable = [
        'org_id',
        'session_id',
        'asset_id',
        'user_id',
        'username',
        'query',
        'command_type',
        'count',
        'first_timestamp',
        'last_timestamp',
    ];

    protected $guarded = [];

    protected $casts = [
        'first_timestamp' => 'datetime',
        'last_timestamp' => 'datetime',
        'count' => 'integer',
        'created_at' => 'datetime',
    ];

    public static $includable = [
        'org',
        'session',
        'asset',
        'user',
    ];

    public function prunable(): Builder
    {
        return static::where('created_at', '<', now()->subDays(365));
    }

    public function org(): BelongsTo
    {
        return $this->belongsTo(Org::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function request(): BelongsTo
    {
        return $this->belongsTo(Request::class);
    }

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Store audit logs for a session
     *
     * @return int Number of audit logs stored
     */
    public static function storeForSession(Session $session, array $queryLogs): int
    {
        try {
            if (empty($queryLogs)) {
                return 0;
            }
            $auditData = [];
            foreach ($queryLogs as $log) {
                $auditData[] = [
                    'org_id' => $session->org_id,
                    'session_id' => $session->id,
                    'asset_id' => $session->asset_id,
                    'user_id' => $session->requester_id,
                    'username' => $log->userHost ?? '',
                    'query' => $log->query ?? '',
                    'command_type' => $log->commandType ?? null,
                    'count' => $log->count ?? 0,
                    'first_timestamp' => $log->firstTimestamp ?? null,
                    'last_timestamp' => $log->lastTimestamp ?? null,
                    'created_at' => now(),
                ];
            }
            static::insert($auditData);
            return count($auditData);
        } catch (\Exception $e) {
            throw new \Exception('Failed to store session audit logs: '.$e->getMessage());
        }
    }

    /**
     * Get stored audit logs for a session
     */
    public static function getForSession(int $sessionId): Collection
    {
        return static::where('session_id', $sessionId)
            ->orderBy('last_timestamp', 'desc')
            ->get();
    }
}
