<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'brand.name' => 'SellPata',
        'brand.domain' => 'sellpata.com',
        'brand.tagline' => 'ফেসবুক উদ্যোক্তাদের জন্য সহজ ব্র্যান্ডেড ল্যান্ডিং পেজ।',
        'youtube_url' => '',
        'template_count_label' => '',
        'features.payments.available' => '0',
        'features.shipping.available' => '0',
        'features.coming_soon_label' => 'শিগগিরই আসছে',
        'contact.phone' => '',
        'contact.email' => '',
        'contact.whatsapp' => '',
        'platform.base_domain' => 'sellpata.com',
        'platform.impersonation_ttl_minutes' => '30',
        'platform.invoice.issuer.name' => 'SellPata',
        'platform.invoice.issuer.address' => '',
        'platform.invoice.issuer.phone' => '',
        'platform.invoice.issuer.email' => '',
        'platform.invoice.vat_note' => '',
    ];

    public static function get(string $key, ?string $default = null): ?string
    {
        $values = Cache::rememberForever('settings.all', fn () => static::query()->pluck('value', 'key')->all());

        if (array_key_exists($key, $values) && $values[$key] !== null) {
            return (string) $values[$key];
        }

        return $default ?? (static::DEFAULTS[$key] ?? null);
    }

    public static function bool(string $key): bool
    {
        return in_array(static::get($key), ['1', 'true', 'on', 'yes'], true);
    }

    public static function put(string $key, ?string $value): void
    {
        static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings.all');
    }

    public static function flush(): void
    {
        Cache::forget('settings.all');
    }
}
