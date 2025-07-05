<?php

namespace App\Http\Requests\SavingsGoal;

use Illuminate\Foundation\Http\FormRequest;

class StoreSavingsGoalRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'target_amount' => 'required|numeric|min:0.01|max:9999999.99',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a goal name.',
            'name.string' => 'Goal name must be text.',
            'name.max' => 'Goal name cannot exceed 255 characters.',
            'target_amount.required' => 'Please enter a target amount.',
            'target_amount.numeric' => 'Target amount must be a valid number.',
            'target_amount.min' => 'Target amount must be at least $0.01.',
            'target_amount.max' => 'Target amount cannot exceed $9,999,999.99.',
            'description.string' => 'Description must be text.',
            'description.max' => 'Description cannot exceed 500 characters.',
        ];
    }
}
