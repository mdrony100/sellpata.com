<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Impersonation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ImpersonationController extends Controller
{
    public function start(Request $request, User $user)
    {
        // No nested impersonation, and never impersonate another administrator.
        abort_if(session('impersonator_id'), 409, 'ইতিমধ্যে একটি সেশন চালু আছে।');
        abort_if($user->is_admin, 403, 'অ্যাডমিন অ্যাকাউন্টে প্রবেশ করা যাবে না।');

        $data = $request->validate(['reason' => ['required', 'string', 'min:3', 'max:200']]);
        $admin = $request->user();

        $session = Impersonation::create([
            'admin_id' => $admin->id,
            'user_id' => $user->id,
            'reason' => $data['reason'],
            'started_at' => now(),
            'actions' => [],
        ]);

        Auth::login($user);
        $request->session()->put([
            'impersonator_id' => $admin->id,
            'impersonation_id' => $session->id,
            'impersonation_expires' => now()->addMinutes(\App\Services\Platform\PlatformSettings::int('platform.impersonation_ttl_minutes', 30))->toIso8601String(),
        ]);
        $request->session()->regenerate();

        AuditLog::record('impersonation.start', $user, ['reason' => $data['reason'], 'session' => $session->id], $admin->id);

        return response()->json(['ok' => true]);
    }

    public function stop(Request $request)
    {
        $adminId = session('impersonator_id');
        abort_unless($adminId, 409);

        $sessionId = session('impersonation_id');
        Impersonation::whereKey($sessionId)->update(['ended_at' => now()]);

        Auth::loginUsingId($adminId);
        $request->session()->forget(['impersonator_id', 'impersonation_id', 'impersonation_expires']);
        $request->session()->regenerate();

        AuditLog::record('impersonation.stop', null, ['session' => $sessionId], $adminId);

        return response()->json(['ok' => true]);
    }
}
