<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Store;
use App\Services\Platform\CustomDomainService;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\Request;

class CustomDomainController extends Controller
{
    public function show(Request $request, CustomDomainService $domains)
    {
        return response()->json($domains->payload($this->customerStore($request)));
    }

    public function store(Request $request, CustomDomainService $domains)
    {
        $store = $this->customerStore($request);

        if (! $domains->allows($store)) {
            return $this->denied($domains);
        }

        $raw = (string) $request->input('domain', '');
        $normalized = $domains->normalize($raw);

        if ($store->custom_domain) {
            if ($normalized === $store->custom_domain) {
                return response()->json($domains->payload($store));
            }

            return response()->json([
                'message' => 'আপনার একটি কাস্টম ডোমেইন আছে। বদলাতে আগে সেটি সরিয়ে দিন।',
                'errors' => ['domain' => ['আপনার একটি কাস্টম ডোমেইন আছে। বদলাতে আগে সেটি সরিয়ে দিন।']],
            ], 422);
        }

        if ($error = $domains->validationError($raw, $store->id)) {
            return response()->json(['message' => $error, 'errors' => ['domain' => [$error]]], 422);
        }

        $store->forceFill([
            'custom_domain' => $normalized,
            'custom_domain_status' => CustomDomainService::PENDING,
            'custom_domain_verified_at' => null,
            'custom_domain_checked_at' => null,
        ]);

        try {
            $store->save();
        } catch (UniqueConstraintViolationException) {
            $error = 'এই ডোমেইনটি অন্য একটি স্টোরে যুক্ত আছে।';

            return response()->json(['message' => $error, 'errors' => ['domain' => [$error]]], 422);
        }

        AuditLog::record('store.domain_added', $store, ['domain' => $store->custom_domain]);

        return response()->json($domains->payload($store), 201);
    }

    public function check(Request $request, CustomDomainService $domains)
    {
        $store = $this->customerStore($request);

        if (! $domains->allows($store)) {
            return $this->denied($domains);
        }

        if (! $store->custom_domain) {
            return response()->json(['message' => 'আগে একটি ডোমেইন যোগ করুন।'], 422);
        }

        $domains->markChecked($store);

        AuditLog::record('store.domain_checked', $store, [
            'domain' => $store->custom_domain,
            'status' => $store->custom_domain_status,
        ]);

        return response()->json($domains->payload($store));
    }

    public function destroy(Request $request, CustomDomainService $domains)
    {
        $store = $this->customerStore($request);
        $previous = $store->custom_domain;

        $store->forceFill([
            'custom_domain' => null,
            'custom_domain_status' => null,
            'custom_domain_verified_at' => null,
            'custom_domain_checked_at' => null,
        ])->save();

        if ($previous) {
            AuditLog::record('store.domain_removed', $store, ['domain' => $previous]);
        }

        return response()->json($domains->payload($store));
    }

    private function denied(CustomDomainService $domains)
    {
        return response()->json(['message' => $domains->deniedMessage()], 403);
    }

    private function customerStore(Request $request): Store
    {
        $store = $request->user()->store;

        abort_unless($store, 404);

        return $store;
    }
}
