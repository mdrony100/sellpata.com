<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;

class MessageController extends Controller
{
    public function index()
    {
        return view('admin.messages.index', [
            'items' => ContactMessage::latest()->paginate(20),
            'editing' => ContactMessage::find(request('show')),
        ]);
    }

    public function handled(ContactMessage $message)
    {
        $message->update(['is_handled' => ! $message->is_handled]);

        return back()->with('status', 'বার্তার অবস্থা আপডেট হয়েছে।');
    }

    public function destroy(ContactMessage $message)
    {
        $message->delete();

        return back()->with('status', 'বার্তা মুছে ফেলা হয়েছে।');
    }
}
