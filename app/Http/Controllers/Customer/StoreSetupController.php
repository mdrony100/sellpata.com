<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Plan;
use App\Models\Store;
use App\Models\Subscription;
use App\Services\Platform\UsernameService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreSetupController extends Controller
{
    public function check(Request $request, UsernameService $usernames)
    {
        $value = (string) $request->query('username', '');

        return response()->json([
            'username' => $usernames->normalize($value),
            'error' => $usernames->error($value),
            'preview' => $usernames->normalize($value) !== ''
                ? $usernames->normalize($value).'.'.config('platform.base_domain')
                : null,
        ]);
    }

    /** First store for this account: Free plan, no payment, username required. */
    public function store(Request $request, UsernameService $usernames)
    {
        $user = $request->user();

        if ($user->store) {
            return response()->json(['ok' => true, 'store' => ['username' => $user->store->username]]);
        }

        $data = $request->validate([
            'username' => ['required', 'string', 'max:40'],
            'display_name' => ['nullable', 'string', 'max:120'],
        ]);

        if ($error = $usernames->error($data['username'])) {
            return response()->json(['message' => $error, 'errors' => ['username' => [$error]]], 422);
        }

        $store = DB::transaction(function () use ($user, $usernames, $data) {
            $username = $usernames->normalize($data['username']);

            // Unique index on username_lower is the real guard against races.
            $store = Store::create([
                'user_id' => $user->id,
                'username' => $username,
                'username_lower' => $username,
                'display_name' => ($data['display_name'] ?? null) ?: ($user->name.'-এর স্টোর'),
            ]);

            Subscription::create([
                'store_id' => $store->id,
                'plan_id' => Plan::free()->id,
                'status' => 'active',
                'source' => 'free',
                'started_at' => now(),
            ]);

            AuditLog::record('store.created', $store, ['username' => $username], $user->id);

            return $store;
        });

        return response()->json([
            'ok' => true,
            'store' => ['username' => $store->username, 'url' => $store->publicUrl()],
        ], 201);
    }
}
