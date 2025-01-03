<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Add authorization logic if needed.
    }

    public function rules()
    {
        return [
            'product_id' => 'required|exists:products,id'
        ];
    }
}
