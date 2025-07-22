<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Set authorization logic as needed
    }

    public function rules()
    {
        return [
            'customer' => 'string',
            'items' => 'array',
            'items.*.product_id' => 'exists:products,id',
            'items.*.count' => 'integer|min:1'
        ];
    }
}
