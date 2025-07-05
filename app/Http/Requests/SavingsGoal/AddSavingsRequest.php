<?php

namespace App\Http\Requests\SavingsGoal;

use App\Enums\FundingSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AddSavingsRequest extends FormRequest
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
            'amount' => 'required|numeric|min:0.01|max:9999999.99',
            'source' => ['required', Rule::enum(FundingSource::class)],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Please enter an amount to add.',
            'amount.numeric' => 'Amount must be a valid number.',
            'amount.min' => 'Amount must be at least $0.01.',
            'amount.max' => 'Amount cannot exceed $9,999,999.99.',
            'source.required' => 'Please select a funding source.',
            'source.enum' => 'Funding source must be either water bank or other.',
        ];
    }
}
