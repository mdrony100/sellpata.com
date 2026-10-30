<?php

namespace App\Services\Platform;

use App\Models\Store;

/**
 * Normalises a customer-supplied domain, decides whether DNS points at this
 * server, and resolves a verified custom domain back to its store.
 */
class CustomDomainService
{
    public const PENDING = 'pending';

    public const VERIFIED = 'verified';

    public const FAILED = 'failed';

    public function __construct(private DnsLookup $dns) {}

    public function normalize(string $input): string
    {
        $value = strtolower(trim($input));

        if ($value === '') {
            return '';
        }

        $value = preg_replace('#^[a-z][a-z0-9+.-]*://#', '', $value) ?? $value;
        $value = preg_replace('#^//+#', '', $value) ?? $value;
        $value = preg_split('/[\/\?#]/', $value, 2)[0] ?? '';

        if (str_contains($value, '@')) {
            $value = substr($value, (int) strrpos($value, '@') + 1);
        }

        $value = rtrim($value, '.');

        if (preg_match('/:\d+$/', $value) === 1) {
            $value = preg_replace('/:\d+$/', '', $value) ?? $value;
        }

        if (str_starts_with($value, 'www.')) {
            $value = substr($value, 4);
        }

        return $value;
    }

    public function validationError(string $raw, ?int $exceptStoreId = null): ?string
    {
        $domain = $this->normalize($raw);

        if ($domain === '') {
            return 'একটি ডোমেইন লিখুন।';
        }

        if (! $this->looksLikeDomain($domain)) {
            return 'সঠিক ডোমেইন লিখুন। যেমন: mybrand.com';
        }

        if ($this->isPlatformHost($domain)) {
            $base = $this->baseDomain();

            return "{$base} বা এর কোনো সাবডোমেইন নিজের ডোমেইন হিসেবে যোগ করা যায় না। আপনার ফ্রি ঠিকানা আগে থেকেই আছে।";
        }

        $taken = Store::query()
            ->where('custom_domain', $domain)
            ->when($exceptStoreId, fn ($query) => $query->where('id', '!=', $exceptStoreId))
            ->exists();

        if ($taken) {
            return 'এই ডোমেইনটি অন্য একটি স্টোরে যুক্ত আছে।';
        }

        return null;
    }

    public function baseDomain(): string
    {
        return strtolower(rtrim((string) PlatformSettings::get('platform.base_domain', 'sellpata.com'), '.'));
    }

    public function serverIp(): string
    {
        $ip = trim((string) config('platform.server_ip', '169.58.90.98'));

        return $ip !== '' ? $ip : '169.58.90.98';
    }

    /** @return list<string> */
    public function platformBases(): array
    {
        return array_values(array_unique(array_filter([
            $this->baseDomain(),
            strtolower(rtrim((string) config('platform.base_domain', 'sellpata.com'), '.')),
        ])));
    }

    public function isPlatformHost(string $host): bool
    {
        $host = strtolower(rtrim($host, '.'));

        if ($host === '') {
            return false;
        }

        foreach ($this->platformBases() as $base) {
            if ($host === $base || str_ends_with($host, '.'.$base)) {
                return true;
            }
        }

        return false;
    }

    public function isLoopbackHost(string $host): bool
    {
        $host = strtolower(trim($host, '[]'));

        return in_array($host, ['localhost', '127.0.0.1', '::1'], true);
    }

    /**
     * A custom domain is verified when the apex A record is this server, or
     * www is a CNAME to the store's free subdomain. Either one is enough.
     */
    public function dnsMatches(Store $store): bool
    {
        $apex = strtolower((string) $store->custom_domain);
        $expectedIp = $this->serverIp();
        $expectedHost = strtolower(rtrim($store->freeHost(), '.'));

        foreach ($this->dns->aRecords($apex) as $ip) {
            if ($ip === $expectedIp) {
                return true;
            }
        }

        foreach ($this->dns->cnameTargets('www.'.$apex) as $target) {
            if (rtrim(strtolower($target), '.') === $expectedHost) {
                return true;
            }
        }

        return false;
    }

    public function markChecked(Store $store): Store
    {
        $ok = $this->dnsMatches($store);

        $store->forceFill([
            'custom_domain_status' => $ok ? self::VERIFIED : self::FAILED,
            'custom_domain_verified_at' => $ok ? now() : null,
            'custom_domain_checked_at' => now(),
        ])->save();

        return $store;
    }

    public function verifiedStoreForHost(string $host): ?Store
    {
        $host = strtolower(rtrim(trim($host), '.'));
        $host = trim($host, '[]');

        if ($host === '' || $this->isLoopbackHost($host) || filter_var($host, FILTER_VALIDATE_IP) || $this->isPlatformHost($host)) {
            return null;
        }

        $apex = str_starts_with($host, 'www.') ? substr($host, 4) : $host;

        if ($apex === '' || $this->isPlatformHost($apex) || filter_var($apex, FILTER_VALIDATE_IP)) {
            return null;
        }

        return Store::query()
            ->where('custom_domain', $apex)
            ->where('custom_domain_status', self::VERIFIED)
            ->first();
    }

    public function payload(Store $store): array
    {
        $freeHost = $store->freeHost();
        $ip = $this->serverIp();

        return [
            'free_host' => $freeHost,
            'base_domain' => $this->baseDomain(),
            'server_ip' => $ip,
            'domain' => $store->custom_domain ? [
                'host' => $store->custom_domain,
                'status' => $store->custom_domain_status,
                'verified_at' => $store->custom_domain_verified_at?->toIso8601String(),
                'checked_at' => $store->custom_domain_checked_at?->toIso8601String(),
            ] : null,
            'records' => [
                ['type' => 'A', 'host' => '@', 'value' => $ip],
                ['type' => 'CNAME', 'host' => 'www', 'value' => $freeHost],
            ],
        ];
    }

    private function looksLikeDomain(string $domain): bool
    {
        if (filter_var($domain, FILTER_VALIDATE_IP)) {
            return false;
        }

        return preg_match(
            '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z]{2,63}$/',
            $domain,
        ) === 1;
    }
}
