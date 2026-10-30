<?php

namespace App\Http\Controllers;

use App\Models\Setting;

class LegalController extends Controller
{
    public function privacy()
    {
        return $this->page('privacy', 'প্রাইভেসি পলিসি', '/privacy-policy');
    }

    public function terms()
    {
        return $this->page('terms', 'শর্তাবলি ও নিয়মাবলি', '/terms');
    }

    public function doc(string $doc)
    {
        abort_unless(in_array($doc, ['privacy', 'terms'], true), 404);

        return response()->json([
            'title' => $this->title($doc),
            'body' => Setting::get("legal.{$doc}.body"),
            'updated_at' => Setting::get("legal.{$doc}.updated"),
        ]);
    }

    private function title(string $doc): string
    {
        return $doc === 'privacy' ? 'প্রাইভেসি পলিসি' : 'শর্তাবলি ও নিয়মাবলি';
    }

    private function page(string $doc, string $title, string $path)
    {
        return view('app', ['seo' => [
            'title' => $title.' — SellPata',
            'description' => $doc === 'privacy'
                ? 'সেলপাতা কী কী তথ্য সংগ্রহ করে, কেন করে এবং আপনার তথ্যের নিয়ন্ত্রণ আপনার হাতে কীভাবে থাকে।'
                : 'সেলপাতা ব্যবহারের শর্ত, প্ল্যান ও পেমেন্ট, কনটেন্টের মালিকানা এবং দায়সীমা।',
            'canonical' => url($path),
            'robots' => 'index,follow',
        ]]);
    }
}
