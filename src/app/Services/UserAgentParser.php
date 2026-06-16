<?php

namespace App\Services;

class UserAgentParser
{
    public function __construct(private readonly string $ua) {}

    public function deviceType(): string
    {
        $ua = strtolower($this->ua);

        if (str_contains($ua, 'tablet') || str_contains($ua, 'ipad')) {
            return 'tablet';
        }

        if (
            str_contains($ua, 'mobile') ||
            str_contains($ua, 'android') ||
            str_contains($ua, 'iphone') ||
            str_contains($ua, 'ipod') ||
            str_contains($ua, 'blackberry') ||
            str_contains($ua, 'windows phone')
        ) {
            return 'mobile';
        }

        return 'desktop';
    }

    public function os(): string
    {
        $ua = strtolower($this->ua);

        return match (true) {
            str_contains($ua, 'windows phone')             => 'Windows Phone',
            str_contains($ua, 'android')                   => 'Android',
            str_contains($ua, 'iphone') || str_contains($ua, 'ipad') || str_contains($ua, 'ipod') => 'iOS',
            str_contains($ua, 'macintosh') || str_contains($ua, 'mac os x') => 'macOS',
            str_contains($ua, 'windows')                   => 'Windows',
            str_contains($ua, 'linux')                     => 'Linux',
            str_contains($ua, 'cros')                      => 'ChromeOS',
            default                                        => 'Otro',
        };
    }

    public function browser(): string
    {
        $ua = $this->ua;

        return match (true) {
            (bool) preg_match('/Edg\/|EdgA\//i', $ua)     => 'Edge',
            (bool) preg_match('/OPR\/|Opera\//i', $ua)    => 'Opera',
            (bool) preg_match('/SamsungBrowser\//i', $ua) => 'Samsung',
            (bool) preg_match('/Firefox\/\d/i', $ua)      => 'Firefox',
            (bool) preg_match('/Chrome\/\d/i', $ua)       => 'Chrome',
            (bool) preg_match('/Safari\/\d/i', $ua)       => 'Safari',
            default                                        => 'Otro',
        };
    }

    public function isBot(): bool
    {
        return (bool) preg_match(
            '/bot|crawl|spider|slurp|curl|wget|python|go-http|java\/|facebookexternalhit|whatsapp/i',
            $this->ua
        );
    }
}
