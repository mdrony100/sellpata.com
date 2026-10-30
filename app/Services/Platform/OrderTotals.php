<?php

namespace App\Services\Platform;

use App\Models\Store;
use Illuminate\Validation\ValidationException;

/**
 * Prices are always recalculated here from stored content — the browser never
 * gets to send a total. Variant, bundle and delivery-area prices are checked
 * against the published page, not the request body.
 */
class OrderTotals
{
    public static function fromPage(Store $store, array $content, int $quantity = 1, array $options = []): array
    {
        $quantity = max(1, min(99, $quantity));
        $mode = $content['selection']['mode'] ?? 'simple';
        if (! in_array($mode, ['simple', 'variants', 'bundles'], true)) {
            $mode = 'simple';
        }

        $itemName = (string) ($content['product']['name'] ?? 'পণ্য');
        $variantLabel = null;
        $variantId = null;
        $bundleId = null;
        $unit = 0;

        if ($mode === 'variants' && self::rows($content['variants'] ?? []) !== []) {
            $row = self::requireRow($content['variants'] ?? [], $options['variant_id'] ?? null, 'variant_id', 'একটি পণ্যের অপশন বেছে নিন।');
            $variantId = (string) $row['id'];
            $variantLabel = (string) ($row['name'] ?? $row['attribute_value'] ?? '');
            $itemName = trim($itemName.($variantLabel !== '' ? ' — '.$variantLabel : ''));
            $unit = self::parsePrice($row['price'] ?? null) ?? 0;
        } elseif ($mode === 'bundles' && self::rows($content['bundles'] ?? []) !== []) {
            $row = self::requireRow($content['bundles'] ?? [], $options['bundle_id'] ?? null, 'bundle_id', 'একটি প্যাকেজ বেছে নিন।');
            $bundleId = (string) $row['id'];
            $variantLabel = (string) ($row['name'] ?? '');
            $itemName = $variantLabel !== '' ? $variantLabel : $itemName;
            $unit = self::parsePrice($row['price'] ?? null) ?? 0;
        } else {
            $unit = self::parsePrice($content['product']['sale_price'] ?? null)
                ?? self::parsePrice($content['product']['price'] ?? null)
                ?? 0;
        }

        $subtotal = $unit * $quantity;
        $discount = self::discount($content, $subtotal);
        [$delivery, $areaName, $areaId] = self::delivery($store, $content, $options['area_id'] ?? null);

        $method = (string) ($options['payment_method'] ?? 'cod');
        $allowed = array_column(CheckoutOptions::paymentMethods($store), 'id');
        if (! in_array($method, $allowed, true)) {
            throw ValidationException::withMessages([
                'payment_method' => 'এই পেমেন্ট পদ্ধতি এখন পেমেন্ট নিতে পারবে না।',
            ]);
        }

        return [
            'quantity' => $quantity,
            'unit_price' => $unit,
            'subtotal' => $subtotal,
            'discount' => $discount,
            'delivery_charge' => $delivery,
            'total' => max(0, $subtotal - $discount + $delivery),
            'item_name' => $itemName,
            'variant_label' => $variantLabel,
            'variant_id' => $variantId,
            'bundle_id' => $bundleId,
            'area_id' => $areaId,
            'area_name' => $areaName,
            'payment_method' => $method,
        ];
    }

    /** Accepts "৯৯০", "990", "৯,৯৯০৳" and returns an integer. */
    public static function parsePrice(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_int($value)) {
            return max(0, $value);
        }

        $digits = self::asciiDigits((string) $value);
        $digits = preg_replace('/[^0-9]/', '', $digits);

        return $digits === '' ? null : (int) $digits;
    }

    public static function asciiDigits(string $value): string
    {
        return str_replace(
            ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'],
            ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'],
            $value
        );
    }

    /** @param  list<mixed>  $rows */
    private static function rows(array $rows): array
    {
        return array_values(array_filter($rows, fn ($row) => is_array($row) && isset($row['id']) && $row['id'] !== ''));
    }

    private static function requireRow(array $rows, mixed $id, string $field, string $missing): array
    {
        $match = null;
        foreach (self::rows($rows) as $row) {
            if ((string) $row['id'] === (string) $id) {
                $match = $row;
                break;
            }
        }

        if ($match === null) {
            throw ValidationException::withMessages([$field => $missing]);
        }

        if (array_key_exists('available', $match) && ! $match['available']) {
            throw ValidationException::withMessages([$field => 'এই অপশনটি এখন নেই। অন্য একটি বেছে নিন।']);
        }

        return $match;
    }

    private static function discount(array $content, int $subtotal): int
    {
        $offer = $content['offer'] ?? null;
        if (! is_array($offer) || empty($offer['active'])) {
            return 0;
        }

        $amount = self::parsePrice($offer['amount'] ?? null) ?? 0;

        return ($amount > 0 && $amount < $subtotal) ? $amount : 0;
    }

    /** @return array{0: int, 1: ?string, 2: ?string} */
    private static function delivery(Store $store, array $content, mixed $areaId): array
    {
        $areas = self::rows($content['delivery']['areas'] ?? []);

        if ($areas !== []) {
            $area = null;
            foreach ($areas as $row) {
                if ((string) $row['id'] === (string) $areaId) {
                    $area = $row;
                    break;
                }
            }

            if ($area === null) {
                throw ValidationException::withMessages([
                    'area_id' => 'ডেলিভারি এলাকা বেছে নিন।',
                ]);
            }

            $charge = $store->delivery_charge_enabled
                ? (self::parsePrice($area['charge'] ?? 0) ?? 0)
                : 0;

            return [$charge, (string) ($area['name'] ?? ''), (string) $area['id']];
        }

        $charge = $store->delivery_charge_enabled ? (int) $store->delivery_charge : 0;

        return [$charge, null, null];
    }
}
