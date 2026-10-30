<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        return view('admin.settings.index', [
            'values' => collect(Setting::DEFAULTS)->mapWithKeys(
                fn ($default, $key) => [$key => Setting::get($key, $default)]
            ),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'brand.name' => ['required', 'string', 'max:60'],
            'brand.domain' => ['required', 'string', 'max:120'],
            'brand.tagline' => ['required', 'string', 'max:200'],
            'youtube_url' => ['nullable', 'url', 'max:255'],
            'template_count_label' => ['nullable', 'string', 'max:120'],
            'features.coming_soon_label' => ['required', 'string', 'max:60'],
            'contact.phone' => ['nullable', 'string', 'max:40'],
            'contact.email' => ['nullable', 'email', 'max:120'],
            'contact.whatsapp' => ['nullable', 'string', 'max:40'],
        ], [], [
            'brand.name' => 'ব্র্যান্ডের নাম',
            'brand.domain' => 'ডোমেইন',
            'brand.tagline' => 'সংক্ষিপ্ত পরিচিতি',
            'youtube_url' => 'ইউটিউব লিংক',
            'contact.email' => 'ইমেইল',
        ]);

        // PHP turns dots in field names into underscores, so the form posts
        // nested arrays instead. They are flattened back into dotted setting
        // keys here: scalar fields keep their own key, grouped fields gain a
        // prefix, and the feature toggles are stored explicitly below.
        foreach ($data as $key => $value) {
            if (! is_array($value)) {
                Setting::put($key, (string) ($value ?? ''));

                continue;
            }

            foreach ($value as $sub => $subValue) {
                if (is_array($subValue)) {
                    continue;
                }

                Setting::put("{$key}.{$sub}", (string) ($subValue ?? ''));
            }
        }

        // Platform fields are optional: an older form post (or a partial one)
        // simply keeps the stored value instead of failing validation.
        $request->validate([
            'platform.base_domain' => ['sometimes', 'nullable', 'string', 'max:120', 'regex:/^[a-z0-9.-]+$/'],
            'platform.impersonation_ttl_minutes' => ['sometimes', 'nullable', 'integer', 'min:5', 'max:240'],
            'platform.invoice_issuer_name' => ['sometimes', 'nullable', 'string', 'max:120'],
            'platform.invoice_issuer_address' => ['sometimes', 'nullable', 'string', 'max:200'],
            'platform.invoice_issuer_phone' => ['sometimes', 'nullable', 'string', 'max:40'],
            'platform.invoice_issuer_email' => ['sometimes', 'nullable', 'email', 'max:120'],
            'platform.invoice_vat_note' => ['sometimes', 'nullable', 'string', 'max:200'],
        ], [], [
            'platform.base_domain' => 'বেস ডোমেইন',
            'platform.impersonation_ttl_minutes' => 'সেশনের সময় (মিনিট)',
            'platform.invoice_issuer_name' => 'প্রদানকারীর নাম',
        ]);

        $map = [
            'base_domain' => 'platform.base_domain',
            'impersonation_ttl_minutes' => 'platform.impersonation_ttl_minutes',
            'invoice_issuer_name' => 'platform.invoice.issuer.name',
            'invoice_issuer_address' => 'platform.invoice.issuer.address',
            'invoice_issuer_phone' => 'platform.invoice.issuer.phone',
            'invoice_issuer_email' => 'platform.invoice.issuer.email',
            'invoice_vat_note' => 'platform.invoice.vat_note',
        ];

        foreach ($map as $field => $key) {
            if (array_key_exists($field, (array) $request->input('platform', []))) {
                Setting::put($key, (string) $request->input("platform.{$field}"));
            }
        }

        Setting::put('features.payments.available', $request->boolean('features.payments.available') ? '1' : '0');
        Setting::put('features.shipping.available', $request->boolean('features.shipping.available') ? '1' : '0');

        return back()->with('status', 'সেটিংস সংরক্ষণ হয়েছে।');
    }
}
