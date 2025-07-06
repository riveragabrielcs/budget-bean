<?php

namespace App\Http\Requests\WaterBank;

use App\Enums\FundingSource;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WaterAllGoalsRequest extends FormRequest
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
            'total_amount' => 'required|numeric|min:0.01|max:9999999.99',
            'source' => ['required', Rule::enum(FundingSource::class)],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'total_amount.required' => 'Please enter a total amount to distribute.',
            'total_amount.numeric' => 'Total amount must be a valid number.',
            'total_amount.min' => 'Total amount must be at least $0.01.',
            'total_amount.max' => 'Total amount cannot exceed $9,999,999.99.',
            'source.required' => 'Please select a funding source.',
            'source.enum' => 'Funding source must be either water bank or other.',
        ];
    }
}
