<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Step;
use Illuminate\Http\Request;

class StepController extends Controller
{
    public function index()
    {
        return view('admin.steps.index', [
            'items' => Step::orderBy('sort_order')->get(),
            'editing' => Step::find(request('edit')),
        ]);
    }

    public function store(Request $request)
    {
        Step::create($this->validated($request));

        return back()->with('status', 'ধাপ যোগ হয়েছে।');
    }

    public function update(Request $request, Step $step)
    {
        $step->update($this->validated($request));

        return redirect()->route('admin.steps.index')->with('status', 'ধাপ আপডেট হয়েছে।');
    }

    public function destroy(Step $step)
    {
        $step->delete();

        return back()->with('status', 'ধাপ মুছে ফেলা হয়েছে।');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'number_label' => ['required', 'string', 'max:10'],
            'title' => ['required', 'string', 'max:120'],
            'body' => ['required', 'string', 'max:300'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:99'],
        ]);

        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_published'] = $request->boolean('is_published');

        return $data;
    }
}
