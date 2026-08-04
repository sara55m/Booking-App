<?php

namespace App\Http\Requests\Reviews;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
        return [
            'booking_reference'=>'required|exists:bookings,reference',
            'rating'=>'required|integer|min:1|max:5',
            'comment'=>'nullable|string|max:1000',

            'review_categories'=>'required|array',
            'review_categories.*.category_id'=>[
                'required',
                'integer',
                'distinct',
                Rule::exists('review_categories', 'id')->where('is_active', true)
            ],
            'review_categories.*.rating'=>'required|integer|min:1|max:5',

            'review_tags'=>'nullable|array',
            'review_tags.*'=>'exists:review_tags,id',
        ];
    }
}
