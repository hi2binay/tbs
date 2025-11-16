<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class CreatePaymentIntentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reservation_id' => ['required', 'integer', 'exists:reservations,id'],
            'payment_method' => ['required', 'string', 'in:stripe,razorpay,paypal,upi'],
        ];
    }

    public function messages(): array
    {
        return [
            'reservation_id.required' => 'Reservation ID is required.',
            'reservation_id.exists' => 'Invalid reservation.',
            'payment_method.required' => 'Payment method is required.',
            'payment_method.in' => 'Invalid payment method selected.',
        ];
    }
}
