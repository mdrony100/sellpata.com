<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class ContactController extends Controller
{
    /**
     * Store a contact message coming from the marketing site.
     *
     * Success is only reported once the row is written, so the frontend never
     * shows a fake confirmation.
     */
    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        $message = ContactMessage::create([
            'name' => $request->string('name')->trim()->toString(),
            'phone' => $request->string('phone')->trim()->toString(),
            'page_url' => $request->filled('page_url')
                ? $request->string('page_url')->trim()->toString()
                : null,
            'message' => $request->string('message')->trim()->toString(),
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);

        return response()->json([
            'ok' => true,
            'id' => $message->id,
            'message' => 'ধন্যবাদ! আপনার বার্তা আমরা পেয়েছি। খুব শিগগিরই যোগাযোগ করব।',
        ], 201);
    }
}
