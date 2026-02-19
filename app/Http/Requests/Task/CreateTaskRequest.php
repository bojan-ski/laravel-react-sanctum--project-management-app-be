<?php

namespace App\Http\Requests\Task;

use Illuminate\Foundation\Http\FormRequest;

class CreateTaskRequest extends FormRequest
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
            'assigned_to' => 'required|integer|exists:users,id',
            'title' => 'required|string|min:3|max:64',
            'description' => 'required|string|min:10|max:1500',
            'priority' => 'required|in:low,medium,high,critical',
            'due_date' => 'required|date',
        ];
    }
}
