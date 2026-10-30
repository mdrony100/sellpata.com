<?php

namespace App\Support;

class PhoneNumber
{
    /**
     * Normalises any common Bangladeshi format to +8801XXXXXXXXX.
     * Returns null when the number cannot be a valid BD mobile number.
     */
    public static function normalize(string $raw): ?string
    {
        $digits = preg_replace('/[^0-9]/', '', $raw) ?? '';

        if (str_starts_with($digits, '00880')) {
            $digits = substr($digits, 2);
        }

        if (str_starts_with($digits, '880')) {
            $digits = substr($digits, 3);
        }

        // Local form: 01XXXXXXXXX — drop the trunk zero first.
        if (preg_match('/^0(1[3-9][0-9]{8})$/', $digits, $matches)) {
            $digits = $matches[1];
        }

        if (! preg_match('/^1[3-9][0-9]{8}$/', $digits)) {
            return null;
        }

        return '+880'.$digits;
    }

    public static function mask(string $normalized): string
    {
        // +8801712345678 -> +880 17•• •••678
        $local = substr($normalized, 4);
        $start = substr($local, 0, 2);
        $end = substr($local, -3);
        $bullets = str_repeat('•', max(0, strlen($local) - 5));

        return '+880 '.$start.$bullets.' '.$end;
    }
}
