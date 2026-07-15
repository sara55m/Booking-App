<?php

namespace App\Http\Requests\Bookings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class CheckoutRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        //access booking via route model binding
        $booking = $this->route('booking');

        return [
            'amount' => ['required', 'numeric', 'min:' . $booking->getMinimumPaymentAmount(),],
            'redeem_points' => ['nullable', 'integer', 'min:0', 'multiple_of:100'],
        ];
    }


    public function messages(): array
    {
        return [
            'amount.min' => __('messages.minimum_payment_error', [
                'amount' => $this->route('booking')->getMinimumPaymentAmount(),
            ]),
        ];
    }
}
