<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:cash,bank,ewallet,other',
            'starting_balance' => 'sometimes|required|numeric|min:0',
            'is_active' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom validation messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Account name is required',
            'name.max' => 'Account name cannot exceed 255 characters',
            'type.required' => 'Account type is required',
            'type.in' => 'Account type must be one of: cash, bank, ewallet, other',
            'starting_balance.required' => 'Starting balance is required',
            'starting_balance.numeric' => 'Starting balance must be a number',
            'starting_balance.min' => 'Starting balance cannot be negative',
            'is_active.boolean' => 'Is active field must be true or false',
        ];
    }
}
