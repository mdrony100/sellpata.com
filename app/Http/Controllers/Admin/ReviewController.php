<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index()
    {
        return view('admin.reviews.index', [
            'items' => Review::latest()->get(),
            'editing' => Review::find(request('edit')),
        ]);
    }

    public function store(Request $request)
    {
        Review::create($this->validated($request));

        return back()->with('status', 'রিভিউ যোগ হয়েছে।');
    }

    public function update(Request $request, Review $review)
    {
        $review->update($this->validated($request));

        return redirect()->route('admin.reviews.index')->with('status', 'রিভিউ আপডেট হয়েছে।');
    }

    public function destroy(Review $review)
    {
        $review->delete();

        return back()->with('status', 'রিভিউ মুছে ফেলা হয়েছে।');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'author' => ['required', 'string', 'max:120'],
            'brand' => ['nullable', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:600'],
            'rating' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);

        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
