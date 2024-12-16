<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateProductRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "store_id"=>"required",
            "category_id"=>"required",
            "name"=>"required|max:255",
            "description"=>"required|max:65535",
            "price"=>"required|numeric",
            "stock_quantity"=>"required|numeric",
            "image_url"=>"required|image|mimes:jpeg,png,jpg",
            "delivery_period"=>"required",
            "discount_value"=>"nullable|numeric",
            "discount_start"=>"nullable",
            "discount_end"=>"nullable|after:discount_start",
        ];
    }
}
