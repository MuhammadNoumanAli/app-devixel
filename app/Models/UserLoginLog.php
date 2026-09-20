<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserLoginLog extends Model
{
    use HasFactory;

    protected $table = 'user_login_logs';

    protected $fillable = [
        'user_id',
        'ip_address',
        'city',
        'region',
        'country',
        'country_code',
        'postal_code',
        'latitude',
        'longitude',
        'user_agent',
        'device',
        'browser',
        'platform',
        'login_at',
        'status',
    ];

    protected $casts = [
        'login_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getLocationSummaryAttribute(): string
    {
        $parts = array_filter([$this->city, $this->region, $this->country]);
        if (empty($parts)) {
            if (in_array($this->ip_address, ['127.0.0.1', '::1'])) {
                return 'Office LAN / Localhost';
            }
            return 'Unknown Location';
        }
        return implode(', ', $parts);
    }

    /**
     * Record a login event for a user with IP and device parsing.
     */
    public static function recordLogin(User $user, ?string $ip = null, ?string $userAgent = null): self
    {
        $ip = $ip ?? request()->ip();
        $userAgent = $userAgent ?? request()->userAgent();

        $geo = self::lookupIpLocation($ip);
        $deviceInfo = self::parseUserAgent($userAgent);

        return self::create([
            'user_id' => $user->id,
            'ip_address' => $ip,
            'city' => $geo['city'] ?? null,
            'region' => $geo['region'] ?? null,
            'country' => $geo['country'] ?? null,
            'country_code' => $geo['country_code'] ?? null,
            'postal_code' => $geo['postal_code'] ?? null,
            'latitude' => $geo['latitude'] ?? null,
            'longitude' => $geo['longitude'] ?? null,
            'user_agent' => $userAgent,
            'device' => $deviceInfo['device'],
            'browser' => $deviceInfo['browser'],
            'platform' => $deviceInfo['platform'],
            'login_at' => now(),
            'status' => 'success',
        ]);
    }

    /**
     * Lookup IP location (safe with timeout so login is never delayed).
     */
    protected static function lookupIpLocation(?string $ip): array
    {
        if (!$ip || in_array($ip, ['127.0.0.1', '::1'])) {
            return [
                'city' => 'Local Office LAN',
                'region' => 'Pakistan',
                'country' => 'Pakistan',
                'country_code' => 'PK',
            ];
        }

        try {
            // Fast IP geolocation via ip-api with 1.5s timeout
            $response = Http::timeout(1.5)->get("http://ip-api.com/json/{$ip}?fields=status,country,countryCode,regionName,city,zip,lat,lon");
            if ($response->successful()) {
                $data = $response->json();
                if (($data['status'] ?? '') === 'success') {
                    return [
                        'city' => $data['city'] ?? null,
                        'region' => $data['regionName'] ?? null,
                        'country' => $data['country'] ?? null,
                        'country_code' => $data['countryCode'] ?? null,
                        'postal_code' => $data['zip'] ?? null,
                        'latitude' => $data['lat'] ?? null,
                        'longitude' => $data['lon'] ?? null,
                    ];
                }
            }
        } catch (\Throwable $e) {
            Log::warning("IP Geolocation lookup failed for IP {$ip}: " . $e->getMessage());
        }

        return [];
    }

    /**
     * Simple parser for device, browser, and OS platform from User-Agent.
     */
    protected static function parseUserAgent(?string $ua): array
    {
        if (!$ua) {
            return ['device' => 'Desktop', 'browser' => 'Unknown', 'platform' => 'Unknown'];
        }

        // Device
        $device = 'Desktop';
        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', $ua)) {
            $device = 'Tablet';
        } elseif (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', $ua)) {
            $device = 'Mobile';
        }

        // Platform / OS
        $platform = 'Unknown OS';
        if (preg_match('/windows|win32/i', $ua)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $ua)) {
            $platform = 'macOS';
        } elseif (preg_match('/linux/i', $ua)) {
            $platform = 'Linux';
        } elseif (preg_match('/android/i', $ua)) {
            $platform = 'Android';
        } elseif (preg_match('/iphone|ipad|ipod/i', $ua)) {
            $platform = 'iOS';
        }

        // Browser
        $browser = 'Unknown Browser';
        if (preg_match('/edg/i', $ua)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome|crios/i', $ua)) {
            $browser = 'Chrome';
        } elseif (preg_match('/firefox|fxios/i', $ua)) {
            $browser = 'Firefox';
        } elseif (preg_match('/safari/i', $ua) && !preg_match('/chrome/i', $ua)) {
            $browser = 'Safari';
        } elseif (preg_match('/opera|opr/i', $ua)) {
            $browser = 'Opera';
        }

        return [
            'device' => $device,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }
}
