<?php

namespace App\Services\Platform;

use App\Models\Store;

/**
 * Prices are always recalculated here from stored content — the browser never
 * gets to send a total.
 */
class OrderTotals
{
    public static function fromPage(Store $store, array $content, int $quantity = 1): array
    {
        $quantity = max(1, min(99, $quantity));

        $unit = self::parsePrice($content['product']['sale_price'] ?? null)
            ?? self::parsePrice($content['product']['price'] ?? null)
            ?? 0;

        $subtotal = $unit * $quantity;
        $delivery = $store->delivery_charge_enabled ? (int) $store->delivery_charge : 0;

        return [
            'quantity' => $quantity,
            'unit_price' => $unit,
            'subtotal' => $subtotal,
            'discount' => 0,
            'delivery_charge' => $delivery,
            'total' => $subtotal + $delivery,
        ];
    }

    /** Accepts "৯৯০", "990", "৯,৯৯০৳" and returns an integer. */
    public static function parsePrice(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $bengali = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
        $digits = str_replace($bengali, range(0, 9), (string) $value);
        $digits = preg_replace('/[^0-9]/', '', $digits);

        return $digits === '' ? null : (int) $digits;
    }
}
