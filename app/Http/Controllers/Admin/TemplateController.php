<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Template;
use Illuminate\Http\Request;

class TemplateController extends Controller
{
    public function index()
    {
        return view('admin.templates.index', [
            'items' => Template::with('category')->orderBy('sort_order')->get(),
            'categories' => Category::orderBy('sort_order')->get(),
            'editing' => Template::find(request('edit')),
        ]);
    }

    public function store(Request $request)
    {
        Template::create($this->validated($request));

        return back()->with('status', 'টেমপ্লেট যোগ হয়েছে।');
    }

    public function update(Request $request, Template $template)
    {
        $template->update($this->validated($request));

        return redirect()->route('admin.templates.index')->with('status', 'টেমপ্লেট আপডেট হয়েছে।');
    }

    public function destroy(Template $template)
    {
        $template->delete();

        return back()->with('status', 'টেমপ্লেট মুছে ফেলা হয়েছে।');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'brand' => ['required', 'string', 'max:120'],
            'headline' => ['required', 'string', 'max:160'],
            'subline' => ['nullable', 'string', 'max:160'],
            'product_name' => ['nullable', 'string', 'max:120'],
            'price' => ['nullable', 'string', 'max:40'],
            'old_price' => ['nullable', 'string', 'max:40'],
            'badge' => ['nullable', 'string', 'max:40'],
            'art' => ['required', 'in:fashion,beauty,food,gadget,home,kids,gift'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:999'],
            'palette.bg' => ['required', 'string', 'max:9'],
            'palette.surface' => ['required', 'string', 'max:9'],
            'palette.ink' => ['required', 'string', 'max:9'],
            'palette.primary' => ['required', 'string', 'max:9'],
            'palette.accent' => ['required', 'string', 'max:9'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_published'] = $request->boolean('is_published');
        $data['palette'] = $request->input('palette');

        return $data;
    }
}
