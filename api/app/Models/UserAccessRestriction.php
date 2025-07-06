<?php

namespace App\Models;

use App\Enums\RestrictionType;
use App\Enums\Status;
use App\Traits\HasBlamable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Request;

class UserAccessRestriction extends Model
{
    use HasBlamable, HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'value',
        'status',
    ];

    protected $casts = [
        'type' => RestrictionType::class,
        'value' => 'array',
        'status' => Status::class,
    ];

    public static $includable = [
        'user',
        'createdBy',
        'updatedBy',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the restriction passes for the given request
     */
    public function passes(Request $request): bool
    {
        if ($this->status !== Status::ACTIVE) {
            return true; // Inactive restrictions always pass
        }

        return match ($this->type) {
            RestrictionType::IP_ADDRESS => $this->passesIpRestriction($request),
            RestrictionType::TIME_WINDOW => $this->passesTimeRestriction(),
            RestrictionType::LOCATION => $this->passesLocationRestriction($request),
            RestrictionType::DEVICE => $this->passesDeviceRestriction($request),
            default => true,
        };
    }

    /**
     * Check if the request passes IP address restrictions
     */
    private function passesIpRestriction(Request $request): bool
    {
        $clientIp = $request->ip();
        $allowedIps = $this->value['allowed_ips'] ?? [];

        // If no IPs specified, restriction passes
        if (empty($allowedIps)) {
            return true;
        }

        foreach ($allowedIps as $allowedIp) {
            if ($this->ipMatches($clientIp, $allowedIp)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if an IP matches a given IP or CIDR
     */
    private function ipMatches(string $ip, string $cidr): bool
    {
        // Handle CIDR notation
        if (strpos($cidr, '/') !== false) {
            [$subnet, $mask] = explode('/', $cidr);

            // Convert IP addresses to decimal format
            $ipLong = ip2long($ip);
            $subnetLong = ip2long($subnet);
            $maskLong = ~((1 << (32 - $mask)) - 1);

            // Check if IP is in subnet
            return ($ipLong & $maskLong) === ($subnetLong & $maskLong);
        }

        // Exact IP match
        return $ip === $cidr;
    }

    /**
     * Check if current time passes time window restrictions
     */
    private function passesTimeRestriction(): bool
    {
        $days = $this->value['days'] ?? [];
        $startTime = $this->value['start_time'] ?? null;
        $endTime = $this->value['end_time'] ?? null;
        $timezone = $this->value['timezone'] ?? 'UTC';

        if (empty($days) || !$startTime || !$endTime) {
            return true;
        }

        // Get current time in the specified timezone
        $now = now()->setTimezone($timezone);
        $currentDay = (int) $now->format('w'); // 0 (Sunday) to 6 (Saturday)
        $currentTime = $now->format('H:i');

        // Check if current day is allowed
        if (!in_array($currentDay, $days)) {
            return false;
        }

        // Check if current time is within allowed window
        return $currentTime >= $startTime && $currentTime <= $endTime;
    }

    /**
     * Check if request passes location restrictions
     */
    private function passesLocationRestriction(Request $request): bool
    {
        $allowedCountries = $this->value['allowed_countries'] ?? [];

        if (empty($allowedCountries)) {
            return true;
        }

        // This would require integration with a geolocation service
        // For now, we'll return true as a placeholder
        // In a real implementation, you would get the country from the IP
        $country = 'US'; // Placeholder

        return in_array($country, $allowedCountries);
    }

    /**
     * Check if request passes device restrictions
     */
    private function passesDeviceRestriction(Request $request): bool
    {
        $allowedDevices = $this->value['allowed_devices'] ?? [];

        if (empty($allowedDevices)) {
            return true;
        }

        $userAgent = $request->userAgent();

        foreach ($allowedDevices as $device) {
            if (stripos($userAgent, $device) !== false) {
                return true;
            }
        }

        return false;
    }
}
