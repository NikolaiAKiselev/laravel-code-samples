<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePurchaseOrder extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'variants'                  => 'required|array',
            'variants.*.id'             => 'required|integer|exists:product_variants,id',
            'variants.*.pivot.quantity' => 'required|numeric',
            'shipping_method'           => 'string|nullable',
            'ordered_by'                => 'integer|exists:users,id',
            'notes'                     => 'nullable|string',
            'supplier_id'               => 'required|integer|exists:suppliers,id',
            'status_id'                 => [
                'required',
                'integer',
                Rule::in(array_pluck(config('purchase_order.status'), 'id')),
            ],
        ];
    }
}
