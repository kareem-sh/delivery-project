<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateStoreRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name" => "required|max:255",
            "name_ar" => "required|max:255",
            "latitude" => "nullable|numeric",
            "longitude" => "nullable|numeric",
            "image" => "nullable",
            "logo_color" => "nullable|max:10",
        ];
    }
}
