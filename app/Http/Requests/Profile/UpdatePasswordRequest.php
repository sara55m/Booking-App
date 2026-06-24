<?php

namespace App\Http\Requests\Profile;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Support\Facades\Hash;

class UpdatePasswordRequest extends FormRequest
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
            'current_password' => ['required','current_password'],
            'new_password' => ['required', 'confirmed',Password::defaults()],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator) {

                if (
                    $this->filled('new_password') &&
                    Hash::check($this->new_password, $this->user()->password)
                ) {
                    $validator->errors()->add(
                        'new_password',
                        __('validation.password_must_be_different')
                    );
                }
            },
        ];
    }
}
