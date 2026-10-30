<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Draft;
use App\Models\DraftMedia;
use App\Support\VisitorIdentity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class DraftMediaController extends Controller
{
    public function store(Request $request, string $publicId)
    {
        $draft = $this->authorize($request, $publicId);

        $request->validate([
            'field' => ['required', 'string', 'max:40'],
            'file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $path = $request->file('file')->store("drafts/{$draft->id}", 'local');

        $media = $draft->media()->create([
            'field' => $request->string('field'),
            'path' => $path,
            'original_name' => $request->file('file')->getClientOriginalName(),
            'mime' => $request->file('file')->getMimeType(),
            'size' => $request->file('file')->getSize(),
        ]);

        return response()->json([
            'id' => $media->id,
            'field' => $media->field,
            'url' => URL::temporarySignedRoute('draft.media.show', now()->addMinutes(30), ['media' => $media->id]),
        ], 201);
    }

    /** Short-lived signed access: no public listing, no long-lived URLs. */
    public function show(Request $request, DraftMedia $media)
    {
        abort_unless(Storage::disk('local')->exists($media->path), 404);

        return Storage::disk('local')->response($media->path, null, [
            'Cache-Control' => 'private, max-age=300',
        ]);
    }

    private function authorize(Request $request, string $publicId): Draft
    {
        $draft = Draft::where('public_id', $publicId)->first();
        abort_if($draft === null, 404);

        $userId = $request->user()?->id;
        $identity = VisitorIdentity::current($request);

        $allowed = ($draft->user_id !== null && $draft->user_id === $userId)
            || ($draft->user_id === null && $draft->anonymous_identity_id !== null
                && $identity !== null && $draft->anonymous_identity_id === $identity->id);

        abort_unless($allowed, 404);

        return $draft;
    }
}
