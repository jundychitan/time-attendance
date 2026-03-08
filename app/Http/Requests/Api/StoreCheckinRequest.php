<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreCheckinRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'id_number' => ['required', 'string', 'exists:employees,id_number'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'location_name' => ['nullable', 'string', 'max:500'],
            'selfie' => ['required', 'image', 'max:5120'],
            'captured_at' => ['required', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'id_number.required' => 'Employee ID number is required.',
            'id_number.exists' => 'No employee found with this ID number.',
            'selfie.required' => 'A selfie photo is required.',
            'selfie.image' => 'The selfie must be a valid image file.',
            'selfie.max' => 'The selfie must not exceed 5MB.',
            'captured_at.required' => 'The capture date and time is required.',
        ];
    }
}
