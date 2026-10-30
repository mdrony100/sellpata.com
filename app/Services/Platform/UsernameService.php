<?php

namespace App\Services\Platform;

use App\Models\Store;

class UsernameService
{
    public const PATTERN = '/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/';

    public function normalize(string $username): string
    {
        return strtolower(trim($username));
    }

    /** @return string|null Bengali error message, or null when valid. */
    public function error(string $username, ?Store $ignore = null): ?string
    {
        $value = $this->normalize($username);
        $min = config('platform.username.min');
        $max = config('platform.username.max');

        if ($value === '') {
            return 'স্টোরের ঠিকানা লিখুন।';
        }
        if (mb_strlen($value) < $min) {
            return "কমপক্ষে {$min} অক্ষরের হতে হবে।";
        }
        if (mb_strlen($value) > $max) {
            return "সর্বোচ্চ {$max} অক্ষর ব্যবহার করা যাবে।";
        }
        if (! preg_match(self::PATTERN, $value)) {
            return 'শুধু ইংরেজি ছোট হাতের অক্ষর, সংখ্যা এবং মাঝে হাইফেন ব্যবহার করুন।';
        }
        if (str_contains($value, '--') || str_starts_with($value, '-') || str_ends_with($value, '-')) {
            return 'হাইফেন একসাথে বা শুরু-শেষে ব্যবহার করা যাবে না।';
        }
        if (in_array($value, config('platform.reserved_usernames'), true)) {
            return 'এই ঠিকানাটি SellPata-র জন্য সংরক্ষিত। অন্য নাম বেছে নিন।';
        }

        $exists = Store::where('username_lower', $value)
            ->when($ignore, fn ($q) => $q->whereKeyNot($ignore->getKey()))
            ->exists();

        return $exists ? 'এই ঠিকানাটি ইতিমধ্যে নেওয়া হয়েছে।' : null;
    }

    public function available(string $username): bool
    {
        return $this->error($username) === null;
    }
}
