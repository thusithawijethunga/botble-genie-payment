<?php

namespace Botble\GeniePayment\Http\Requests;

use Botble\Support\Http\Requests\Request;

class GeniePaymentCallbackRequest extends Request
{
    public function rules(): array
    {
        return [
            'amount' => 'required|numeric',
            'currency' => 'required|string|max:3',
            'transactionId' => 'required|string',
            'signature' => 'required|string',
            'state' => 'nullable|string',
            'order_id' => 'nullable',
            'customer_id' => 'nullable',
            'customer_type' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'amount.required' => 'The payment amount is required.',
            'amount.numeric' => 'The payment amount must be a valid number.',
            'currency.required' => 'The payment currency is required.',
            'currency.string' => 'The payment currency must be a valid currency code.',
            'currency.max' => 'The payment currency must not exceed 3 characters.',
            'transactionId.required' => 'The transaction ID is required.',
            'transactionId.string' => 'The transaction ID must be a valid string.',
            'signature.required' => 'The payment signature is required for verification.',
            'signature.string' => 'The payment signature must be a valid string.',
        ];
    }
}