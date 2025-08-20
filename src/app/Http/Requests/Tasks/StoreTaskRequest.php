<?php

namespace App\Http\Requests\Tasks;

use App\Domain\Tasks\Enum\TaskStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['nullable', Rule::enum(TaskStatus::class)],
            'due_at'      => ['nullable', 'date'],
        ];
    }
}
