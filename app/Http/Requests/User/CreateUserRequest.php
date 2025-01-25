<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{

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
            'full_name' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string',
            'lang' => 'nullable|string|in:ar,en',
            'is_verified' => 'nullable|string',
            'role' => 'nullable|string|in:user,admin,store_manager',
            'allow_gps' => 'nullable|boolean',
            'allow_notifications' => 'nullable|boolean',
            'theme_color' => 'nullable|string|in:dark,light',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ];
    }
}
