<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {

        $rules = [];

        if ($this->filled('fullName')) {
            $rules['fullName'] = 'required|string';
        }
        if ($this->filled('email')) {
            $rules['email'] = 'required|email';
        }
        if ($this->filled('subject')) {
            $rules['subject'] = 'required';
        }
        if ($this->filled('message')) {
            $rules['message'] = 'required';
        }
        return $rules;

    }
}
