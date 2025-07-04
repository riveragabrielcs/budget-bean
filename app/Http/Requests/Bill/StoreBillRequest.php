<?php

namespace App\Http\Requests\Bill;

use Illuminate\Foundation\Http\FormRequest;

class StoreBillRequest extends FormRequest
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
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'bill_date' => 'nullable|integer|min:1|max:31',
            'description' => 'nullable|string|max:500',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a bill name.',
            'name.string' => 'Bill name must be text.',
            'name.max' => 'Bill name cannot exceed 255 characters.',
            'amount.required' => 'Please enter a bill amount.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least $0.01.',
            'amount.max' => 'Amount cannot exceed $9,999,999.99.',
            'bill_date.integer' => 'Bill date must be a valid day number.',
            'bill_date.min' => 'Bill date must be between 1 and 31.',
            'bill_date.max' => 'Bill date must be between 1 and 31.',
            'description.string' => 'Description must be text.',
            'description.max' => 'Description cannot exceed 500 characters.',
        ];
    }
}
