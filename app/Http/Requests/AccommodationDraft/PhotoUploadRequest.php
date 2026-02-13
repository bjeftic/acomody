<?php

namespace App\Http\Requests\AccommodationDraft;

use Illuminate\Foundation\Http\FormRequest;

class PhotoUploadRequest extends FormRequest
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
            'photos' => 'required|array|min:1|max:20',
            'photos.*' => [
                'required',
                'file',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:10240', // 10MB max
                'dimensions:min_width=800,min_height=600',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'photos.required' => 'Please select at least one photo to upload.',
            'photos.array' => 'Invalid photos format.',
            'photos.min' => 'Please select at least one photo.',
            'photos.max' => 'You can upload a maximum of 20 photos at once.',

            'photos.*.required' => 'Photo file is required.',
            'photos.*.file' => 'The uploaded item must be a valid file.',
            'photos.*.image' => 'The file must be an image.',
            'photos.*.mimes' => 'Only JPEG, JPG, PNG, and WebP images are allowed.',
            'photos.*.max' => 'Each photo must not exceed 10MB in size.',
            'photos.*.dimensions' => 'Photos must be at least 800x600 pixels.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'photos' => 'photos',
            'photos.*' => 'photo',
        ];
    }
}
