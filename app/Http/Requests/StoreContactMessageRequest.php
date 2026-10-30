<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContactMessageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:2', 'max:120'],
            // Bangladeshi mobile numbers, with or without the +880 prefix.
            'phone' => ['required', 'string', 'max:20', 'regex:/^(?:\+?88)?01[3-9]\d{8}$/'],
            'page_url' => ['nullable', 'string', 'max:255', 'url'],
            'message' => ['required', 'string', 'min:5', 'max:2000'],
        ];
    }

    /**
     * Bengali validation messages, so the form never mixes languages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'আপনার নাম লিখুন।',
            'name.min' => 'নাম অন্তত ২ অক্ষরের হতে হবে।',
            'name.max' => 'নাম ১২০ অক্ষরের মধ্যে রাখুন।',
            'phone.required' => 'মোবাইল নম্বর লিখুন।',
            'phone.regex' => 'সঠিক মোবাইল নম্বর লিখুন, যেমন ০১৭XXXXXXXX।',
            'phone.max' => 'মোবাইল নম্বর অনেক বড় হয়ে গেছে।',
            'page_url.url' => 'ফেসবুক পেজের লিংকটি সঠিকভাবে লিখুন (https:// দিয়ে শুরু করুন)।',
            'page_url.max' => 'লিংকটি অত্যন্ত বড়। ছোট লিংক ব্যবহার করুন।',
            'message.required' => 'আপনার বার্তা লিখুন।',
            'message.min' => 'বার্তাটি অন্তত ৫ অক্ষরের হতে হবে।',
            'message.max' => 'বার্তা ২০০০ অক্ষরের মধ্যে রাখুন।',
        ];
    }

    /**
     * Normalise the phone number before validation runs.
     */
    protected function prepareForValidation(): void
    {
        if ($this->filled('phone')) {
            $this->merge([
                'phone' => preg_replace('/[\s\-()]+/', '', (string) $this->input('phone')),
            ]);
        }
    }
}
