<?php

namespace App\Http\Requests\Expense;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExpenseRequest extends FormRequest
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
            'amount' => 'sometimes|required|numeric|min:0.01|max:9999999.99',
            'description' => 'sometimes|nullable|string|max:500',
            'expense_date' => 'sometimes|nullable|date|before_or_equal:today',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter an expense name.',
            'name.string' => 'Expense name must be text.',
            'name.max' => 'Expense name cannot exceed 255 characters.',
            'amount.required' => 'Please enter an expense amount.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least $0.01.',
            'amount.max' => 'Amount cannot exceed $9,999,999.99.',
            'description.string' => 'Description must be text.',
            'description.max' => 'Description cannot exceed 500 characters.',
            'expense_date.date' => 'Please enter a valid date.',
            'expense_date.before_or_equal' => 'Expense date cannot be in the future.',
        ];
    }
}
