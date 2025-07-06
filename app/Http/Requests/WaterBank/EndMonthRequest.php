<?php

namespace App\Http\Requests\WaterBank;

use Illuminate\Foundation\Http\FormRequest;

class EndMonthRequest extends FormRequest
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
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2030',
            'override_existing' => 'sometimes|boolean',
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'month.required' => 'Please select a month.',
            'month.integer' => 'Month must be a valid number.',
            'month.min' => 'Month must be between 1 and 12.',
            'month.max' => 'Month must be between 1 and 12.',
            'year.required' => 'Please select a year.',
            'year.integer' => 'Year must be a valid number.',
            'year.min' => 'Year must be between 2020 and 2030.',
            'year.max' => 'Year must be between 2020 and 2030.',
            'override_existing.boolean' => 'Override existing must be true or false.',
        ];
    }
}
