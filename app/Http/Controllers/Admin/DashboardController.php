<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Benefit;
use App\Models\Category;
use App\Models\Client;
use App\Models\ContactMessage;
use App\Models\Review;
use App\Models\Step;
use App\Models\Template;

class DashboardController extends Controller
{
    public function __invoke()
    {
        return view('admin.dashboard', [
            'counts' => [
                'templates' => Template::count(),
                'categories' => Category::count(),
                'benefits' => Benefit::count(),
                'steps' => Step::count(),
                'clients' => Client::count(),
                'reviews' => Review::count(),
                'messages' => ContactMessage::count(),
                'unread' => ContactMessage::where('is_handled', false)->count(),
            ],
        ]);
    }
}
