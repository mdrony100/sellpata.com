<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use Illuminate\Http\Request;

class BenefitController extends Controller
{
    public function index()
    {
        return view('admin.benefits.index', [
            'items' => Benefit::orderBy('sort_order')->get(),
            'editing' => Benefit::find(request('edit')),
        ]);
    }

    public function store(Request $request)
    {
        Benefit::create($this->validated($request));

        return back()->with('status', 'সুবিধা যোগ হয়েছে।');
    }

    public function update(Request $request, Benefit $benefit)
    {
        $benefit->update($this->validated($request));

        return redirect()->route('admin.benefits.index')->with('status', 'সুবিধা আপডেট হয়েছে।');
    }

    public function destroy(Benefit $benefit)
    {
        $benefit->delete();

        return back()->with('status', 'সুবিধা মুছে ফেলা হয়েছে।');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:300'],
            'icon' => ['required', 'in:puzzle,grid,sliders,card,truck,spark'],
            'feature_key' => ['nullable', 'in:payments,shipping'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
