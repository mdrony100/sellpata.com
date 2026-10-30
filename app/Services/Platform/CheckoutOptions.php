<?php

namespace App\Services\Platform;

use App\Models\Store;

/**
 * What a buyer is actually allowed to do at checkout.
 * A plan flag never reveals a payment method, courier, or pixel that cannot run.
 */
class CheckoutOptions
{
    /** @return list<array{id: string, label: string, hint: string, processes: bool}> */
    public static function paymentMethods(Store $store): array
    {
        $methods = [[
            'id' => 'cod',
            'label' => 'ক্যাশ অন ডেলিভারি',
            'hint' => 'পণ্য হাতে পেয়ে টাকা দেবেন। অর্ডার করলেই পরিশোধ হয় না।',
            'processes' => true,
        ]];

        if ($store->plan()->has('online_payment') && self::gatewayReady()) {
            $driver = (string) config('services.sellpata_payments.driver');
            $methods[] = [
                'id' => 'online',
                'label' => 'অনলাইন পেমেন্ট',
                'hint' => $driver.' গেটওয়ে দিয়ে পরিশোধ। অর্ডার আলাদা, পেমেন্ট আলাদা।',
                'processes' => true,
            ];
        }

        return $methods;
    }

    public static function gatewayReady(): bool
    {
        return (bool) config('sellpata.features.payments.available')
            && filled(config('services.sellpata_payments.driver'))
            && filled(config('services.sellpata_payments.key'));
    }

    public static function courierAvailable(Store $store): bool
    {
        return $store->plan()->has('courier')
            && (bool) config('sellpata.features.shipping.available');
    }

    /** @return array{meta: ?string, ga4: ?string} */
    public static function tracking(Store $store, array $content): array
    {
        $tracking = is_array($content['tracking'] ?? null) ? $content['tracking'] : [];
        $meta = isset($tracking['meta_pixel']) ? (string) $tracking['meta_pixel'] : '';
        $ga = isset($tracking['ga4']) ? (string) $tracking['ga4'] : '';

        return [
            'meta' => ($store->plan()->has('meta_pixel') && preg_match('/^\d{5,20}$/', $meta)) ? $meta : null,
            'ga4' => ($store->plan()->has('ga4') && preg_match('/^G-[A-Z0-9]{4,20}$/', $ga)) ? $ga : null,
        ];
    }

    /** Remove pixel ids from the public payload when the plan cannot use them. */
    public static function publicContent(Store $store, array $content): array
    {
        if (! $store->plan()->has('meta_pixel')) {
            unset($content['tracking']['meta_pixel']);
        }
        if (! $store->plan()->has('ga4')) {
            unset($content['tracking']['ga4']);
        }
        if (isset($content['tracking']) && $content['tracking'] === []) {
            unset($content['tracking']);
        }

        if (isset($content['reviews']) && is_array($content['reviews'])) {
            $content['reviews'] = array_values(array_filter(
                $content['reviews'],
                fn ($review) => is_array($review) && empty($review['sample']) && trim((string) ($review['text'] ?? '')) !== ''
            ));
        }

        return $content;
    }
}
