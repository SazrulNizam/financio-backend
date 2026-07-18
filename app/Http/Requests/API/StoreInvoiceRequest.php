<?php

namespace App\Http\Requests\API;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'invoice_number' => [
                'required', 'string', 'max:50',
                Rule::unique('invoices')->where(function ($query) {
                    return $query
                        ->where('customer_name', $this->customer_name)
                        ->whereYear('invoice_date', date('Y', strtotime($this->invoice_date)))
                        ->whereNull('deleted_at');
                }),
            ],
            'customer_name' => 'required|string|max:255',
            'invoice_date'  => 'required|date',
            'reference'     => 'nullable|string|max:255',

            'items'                => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.unit_price'   => 'required|numeric|min:0',
            'items.*.quantity'     => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'invoice_number.unique' => 'Invoice number already exists for this customer in the same year.',
            'items.required'        => 'At least one line item is required.',
            'items.min'             => 'At least one line item is required.',
        ];
    }
}
