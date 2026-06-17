<?php

namespace App\Models;

use App\Services\UserAgentParser;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CardScan extends Model
{
    protected $fillable = [
        'employee_id', 'branch_id', 'ip_address', 'user_agent',
        'device_type', 'os', 'browser',
        'country', 'city', 'referrer',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public static function recordForEmployee(int $employeeId, string $ip, ?string $ua, ?string $referrer): void
    {
        static::recordScan(['employee_id' => $employeeId], $ip, $ua, $referrer);
    }

    public static function recordForBranch(int $branchId, string $ip, ?string $ua, ?string $referrer): void
    {
        static::recordScan(['branch_id' => $branchId], $ip, $ua, $referrer);
    }

    private static function recordScan(array $subject, string $ip, ?string $ua, ?string $referrer): void
    {
        $parser = new UserAgentParser($ua ?? '');

        if ($parser->isBot()) {
            return;
        }

        $geo = static::geoLookup($ip);

        static::create(array_merge($subject, [
            'ip_address'  => $ip,
            'user_agent'  => $ua,
            'device_type' => $parser->deviceType(),
            'os'          => $parser->os(),
            'browser'     => $parser->browser(),
            'country'     => $geo['country'] ?? null,
            'city'        => $geo['city'] ?? null,
            'referrer'    => $referrer ? substr($referrer, 0, 500) : null,
        ]));
    }

    private static function geoLookup(string $ip): array
    {
        if (
            in_array($ip, ['127.0.0.1', '::1'], true) ||
            str_starts_with($ip, '192.168.') ||
            str_starts_with($ip, '10.') ||
            str_starts_with($ip, '172.')
        ) {
            return ['country' => 'Local', 'city' => 'Local'];
        }

        try {
            $ctx  = stream_context_create(['http' => ['timeout' => 2]]);
            $json = @file_get_contents(
                "http://ip-api.com/json/{$ip}?fields=status,country,city",
                false,
                $ctx
            );

            if ($json === false) {
                return [];
            }

            $data = json_decode($json, true);

            return ($data['status'] ?? '') === 'success'
                ? ['country' => $data['country'], 'city' => $data['city']]
                : [];
        } catch (\Throwable) {
            return [];
        }
    }
}
