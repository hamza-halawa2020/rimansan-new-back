<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Gate;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
            'name' => 'sometimes|required|string',
            'email' => [
                'sometimes',
                'required',
                'string',
                'email' . $this->route('user'),
            ],
            'image' => Gate::allows('is-admin') ? 'nullable' : 'sometimes|required|image|max:10240',
            'type' => 'sometimes|required|string',
            'phone' => [
                'sometimes',
                'required',
                'min:8',
                'numeric' . $this->route('id'),
            ],
            'password' => Gate::allows('is-admin') ? 'nullable' : 'sometimes|string|min:8|confirmed',
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
