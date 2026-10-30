<?php

namespace App\Services\Platform;

use App\Models\Setting;

/**
 * Admin-editable platform settings with the config file as a fallback, so an
 * operator can change issuer details or session limits without a deploy.
 */
final class PlatformSettings
{
    public static function get(string $key, ?string $fallback = null): ?string
    {
        $value = Setting::get($key);

        if ($value !== null && trim($value) !== '') {
            return $value;
        }

        $config = config($key);

        return $config !== null && $config !== '' ? (string) $config : $fallback;
    }

    public static function int(string $key, int $fallback): int
    {
        $value = self::get($key);

        return is_numeric($value) ? (int) $value : $fallback;
    }
}
