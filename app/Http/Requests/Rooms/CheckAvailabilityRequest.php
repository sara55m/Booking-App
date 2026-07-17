<?php

namespace App\Http\Requests\Rooms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckAvailabilityRequest extends FormRequest
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
        $property = $this->route('property');

        return [
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guests_number' => 'nullable|integer|min:1',
        
            'room_type_id' => [
                'nullable',
                'integer',
                Rule::exists('room_types', 'id')
                    ->where(fn ($query) => $query->where('property_id', $property->id)),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'room_type_id.exists' => __('messages.room_type_not_belongs_to_property'),
        ];
    }
}
