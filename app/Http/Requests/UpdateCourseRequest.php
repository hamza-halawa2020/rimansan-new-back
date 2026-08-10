<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateCourseRequest extends FormRequest
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
            'title' => 'string',
            'description' => 'string',
            'video_url' => 'url',
            'certifications' => 'sometimes|in:true,false,1,0',
            'price' => 'numeric|min:0',
            'instructor_id' => 'exists:instructors,id',
            'category_id' => 'exists:categories,id',
            'tag_id' => 'exists:tags,id',
            'image' => 'sometimes|nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
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
