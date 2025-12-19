<?php

namespace App\Http\Requests;

use App\Enums\JobListingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateJobListingRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|min:100',
            'location' => 'sometimes|string|max:255',
            'salary_range' => 'sometimes|string|max:100',
            'is_remote' => 'sometimes|boolean',
            'status' => ['sometimes', Rule::in(JobListingStatus::values())],
        ];
    }
}
