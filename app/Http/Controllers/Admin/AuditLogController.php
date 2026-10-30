<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Impersonation;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->query('q'));

        $logs = AuditLog::with('actor')
            ->when($search, fn ($q) => $q->where(fn ($w) => $w->where('action', 'like', "%{$search}%")
                ->orWhere('subject_type', 'like', "%{$search}%")))
            ->latest()
            ->paginate(40)
            ->withQueryString();

        return view('admin.audit.index', [
            'logs' => $logs,
            'search' => $search,
            'impersonations' => Impersonation::with(['admin', 'user'])->latest()->limit(10)->get(),
        ]);
    }
}
