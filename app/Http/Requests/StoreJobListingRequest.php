<?php

namespace App\Http\Requests;

use App\Enums\JobListingStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreJobListingRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string|min:100',
            'location' => 'required|string|max:255',
            'salary_range' => 'required|string|max:100',
            'is_remote' => 'required|boolean',
            'status' => ['required', Rule::in(JobListingStatus::values())],
        ];
    }
}
