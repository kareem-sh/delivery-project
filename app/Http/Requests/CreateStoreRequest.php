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
            "user_id"=>"required|numeric",
            "name"=>"required|max:255",
            "latitude"=>"required|numeric",
            "longitude"=>"required|numeric",
            "image"=>"required|image|mimes:jpeg,png,jpg",
            "logo_color"=>"required|max:10",
        ];
    }
}
