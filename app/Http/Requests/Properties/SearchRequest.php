<?php

namespace App\Http\Requests\Properties;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class SearchRequest extends FormRequest
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
            'search' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'rating' => ['nullable', 'numeric', 'between:1,5'],

            'min_price' => ['nullable', 'numeric', 'min:0'],

            'max_price' => [
                'nullable',
                'numeric',
                'gte:min_price',
            ],
            'sort' => [
            'nullable',
            'in:newest,price_asc,price_desc,rating',
            ],
            'property_amenities' => ['nullable', 'array'],
            'property_amenities.*' => [
                'integer',
                'exists:amenities,id',
            ],

            'room_amenities' => ['nullable', 'array'],
            'room_amenities.*' => [
                'integer',
                'exists:amenities,id',
            ],
            'guests' => ['nullable', 'integer', 'min:1'],
            'check_in' => [
                'nullable',
                'date',
                'after_or_equal:today',
                'required_with:check_out',
            ],

            'check_out' => [
                'nullable',
                'date',
                'after:check_in',
                'required_with:check_in',
            ],
            'page' => ['nullable', 'integer'],
        ];
    }
}
