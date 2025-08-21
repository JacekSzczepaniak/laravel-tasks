<?php

namespace App\Http\Requests\Tasks;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Domain\Tasks\Enum\TaskStatus;
use App\Infrastructure\Tasks\Models\Task;

class UpdateTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::enum(TaskStatus::class)],
            'due_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($v) {
            /** @var Task|null $task */
            $task = $this->route('task');

            if (!$task || !$this->has('status')) {
                return;
            }

            $parsed = TaskStatus::tryFrom($this->input('status'));
            if (!$parsed) {
                return;
            }

            if (!$task->status->canTransition($parsed)) {
                $v->errors()->add('status', __('Invalid status transition.'));
            }
        });
    }

}
