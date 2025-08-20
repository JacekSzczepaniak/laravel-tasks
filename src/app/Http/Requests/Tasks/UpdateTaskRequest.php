<?php

namespace App\Http\Requests\Tasks;

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

    public function rules(): array
    {
        return [
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', Rule::enum(TaskStatus::class)],
            'due_at' => ['sometimes', 'nullable', 'date'],
        ];
    }

    public function withValidator($validator)
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

            if (method_exists(TaskStatus::class, 'canTransition')
                && !TaskStatus::canTransition($task->status, $parsed)) {
                $v->errors()->add('status', __('Invalid status transition.'));
            }
        });
    }
}
