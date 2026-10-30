<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ClientController extends Controller
{
    public function index()
    {
        return view('admin.clients.index', [
            'items' => Client::orderBy('sort_order')->get(),
            'editing' => Client::find(request('edit')),
        ]);
    }

    public function store(Request $request)
    {
        Client::create($this->validated($request));

        return back()->with('status', 'ক্লায়েন্ট যোগ হয়েছে।');
    }

    public function update(Request $request, Client $client)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            if ($client->image_path) {
                Storage::disk('public')->delete($client->image_path);
            }
            $data['image_path'] = $request->file('image')->store('clients', 'public');
        }

        $client->update($data);

        return redirect()->route('admin.clients.index')->with('status', 'ক্লায়েন্ট আপডেট হয়েছে।');
    }

    public function destroy(Client $client)
    {
        if ($client->image_path) {
            Storage::disk('public')->delete($client->image_path);
        }

        $client->delete();

        return back()->with('status', 'ক্লায়েন্ট মুছে ফেলা হয়েছে।');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'brand' => ['required', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:120'],
            'url' => ['nullable', 'url', 'max:255'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ], [], ['image' => 'ছবি']);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_published'] = $request->boolean('is_published');
        unset($data['image']);

        return $data;
    }
}
