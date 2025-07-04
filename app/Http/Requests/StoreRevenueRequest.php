<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRevenueRequest extends FormRequest
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
        $rules = [
            'calculation_method' => 'required|in:paycheck,custom',
            'total_revenue' => 'required|numeric|min:0|max:9999999.99',
            'paycheck_amount' => 'nullable|numeric|min:0|max:9999999.99',
            'paycheck_count' => 'nullable|integer|min:1|max:100',
        ];

        // If paycheck method, make paycheck fields required
        if ($this->input('calculation_method') === 'paycheck') {
            $rules['paycheck_amount'] = 'required|numeric|min:0.01|max:9999999.99';
            $rules['paycheck_count'] = 'required|integer|min:1|max:100';
        }

        return $rules;
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'calculation_method.required' => 'Please select how you want to calculate your revenue.',
            'calculation_method.in' => 'Please select a valid calculation method.',
            'total_revenue.required' => 'Please enter your total monthly revenue.',
            'total_revenue.numeric' => 'Revenue must be a valid number.',
            'total_revenue.min' => 'Revenue cannot be negative.',
            'total_revenue.max' => 'Revenue cannot exceed $9,999,999.99.',
            'paycheck_amount.required' => 'Paycheck amount is required when using paycheck calculation.',
            'paycheck_amount.min' => 'Paycheck amount must be at least $0.01.',
            'paycheck_count.required' => 'Number of paychecks is required when using paycheck calculation.',
            'paycheck_count.min' => 'You must have at least 1 paycheck per month.',
            'paycheck_count.max' => 'Cannot exceed 100 paychecks per month.',
        ];
    }
}
