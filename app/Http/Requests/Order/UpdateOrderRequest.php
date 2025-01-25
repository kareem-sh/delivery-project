<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function rules()
    {
        return [
            'order_items' => 'array',
            'order_items.*.product_id' => 'exists:products,id',
            'order_items.*.quantity' => 'required|integer|min:1',
        ];
    }
}
