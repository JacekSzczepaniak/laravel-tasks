<?php

namespace App\Http\Resources\V1;

use App\Infrastructure\Tasks\Models\Task;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class TaskResource extends JsonResource
{

    /**
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        /** @var Task $task */
        $task = $this->resource;

        $id = $task->id ?? null;
        $ownerId = $task->ownerId ?? $task->user_id ?? null;
        $status = $task->status->value;

        $rawDue = $task->dueAt ?? $task->due_at ?? null;
        $dueIso = $rawDue ? Carbon::parse($rawDue)->toISOString() : null;

        return [
            'id' => $id,
            'owner_id' => $ownerId,
            'title' => $task->title,
            'description' => $task->description,
            'status' => $status,
            'due_at' => $dueIso,
            'created_at' => isset($task->created_at) ? Carbon::parse($task->created_at)->toISOString() : null,
            'updated_at' => isset($task->updated_at) ? Carbon::parse($task->updated_at)->toISOString() : null,
        ];
    }
}
