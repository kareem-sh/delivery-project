<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateStoreRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "name"=>"sometimes|max:255",
            "latitude"=>"sometimes",
            "longitude"=>"sometimes",
            "image"=>"sometimes",
            "logo_color"=>"sometimes|max:10",
        ];
    }
}
