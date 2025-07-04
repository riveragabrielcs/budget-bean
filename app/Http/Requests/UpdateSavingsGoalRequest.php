<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSavingsGoalRequest extends FormRequest
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
     */
    public function rules(): array
    {
        return [
            'monthly_savings_goal' => 'required|numeric|min:0|max:9999999.99',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'monthly_savings_goal.required' => 'Please enter your monthly savings goal.',
            'monthly_savings_goal.numeric' => 'Savings goal must be a valid number.',
            'monthly_savings_goal.min' => 'Savings goal cannot be negative.',
            'monthly_savings_goal.max' => 'Savings goal cannot exceed $9,999,999.99.',
        ];
    }
}
