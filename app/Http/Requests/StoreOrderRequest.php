<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOrderRequest extends FormRequest
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
        return [
            'name' => auth()->check() ? 'nullable' : 'required|string',
            'email' => auth()->check() ? 'nullable' : 'required|email',
            'phone' => auth()->check() ? 'nullable' : 'required|min:8|numeric',
            'address_id' => 'nullable|exists:addresses,id',
            'country_id' => 'nullable|exists:countries,id',
            'city_id' => 'required|exists:cities,id',
            'address' => 'nullable|string',
            'coupon_id' => 'nullable',
            'notes' => 'nullable|string',
            'payment_method' => 'required|in:cash_on_delivery,visa,vodafone_cash',
        ];

    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'message' => 'Validation error',
                'errors' => $validator->errors(),
            ], 422)
        );
    }
}
