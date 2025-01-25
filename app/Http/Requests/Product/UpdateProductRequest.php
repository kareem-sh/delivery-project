<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            "store_id" => "sometimes",
            "category_id" => "sometimes",
            "name" => "sometimes|max:255",
            "name_ar" => "sometimes|max:255",
            "description" => "sometimes|max:65535",
            "description_ar" => "sometimes|max:65535",
            "price" => "sometimes|numeric",
            "stock_quantity" => "sometimes|numeric",
            "image_url" => "sometimes",
            "delivery_period" => "sometimes",
            "discount_value" => "nullable|numeric|min:1|max:100",
            "discount_start" => "nullable|date",
            "discount_end" => "nullable|date|after:discount_start",
        ];
    }
}
