<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class SearchProjectsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Sanitize search term to prevent wildcard injection
     */
    protected function sanitizeSearchTerm(string $searchTerm): string
    {
        $searchTerm = trim($searchTerm);
        $searchTerm = preg_replace('/\s+/', ' ', $searchTerm);
        $searchTerm = str_replace(['%', '_'], ['\%', '\_'], $searchTerm);

        return $searchTerm;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        if ($this->has('search') && $this->search !== null) {
            $this->merge([
                'search' => $this->sanitizeSearchTerm($this->search),
            ]);
        }
    }

    /**
     * Handle a failed validation attempt
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422)
        );
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'search' => 'nullable|string|max:255',
        ];
    }
}
