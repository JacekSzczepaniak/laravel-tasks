<?php

namespace App\Http\Resources\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class TaskResource extends JsonResource
{
    public function toArray($request): array
    {
        $id = $this->id ?? null;
        $ownerId = $this->ownerId ?? $this->user_id ?? null;

        $status = $this->status instanceof \BackedEnum
            ? $this->status->value
            : (string) $this->status;

        $rawDue = $this->dueAt ?? $this->due_at ?? null;
        $dueIso = $rawDue ? Carbon::parse($rawDue)->toISOString() : null;

        $createdIso = isset($this->created_at) ? Carbon::parse($this->created_at)->toISOString() : null;
        $updatedIso = isset($this->updated_at) ? Carbon::parse($this->updated_at)->toISOString() : null;

        return [
            'id' => $id,
            'owner_id' => $ownerId,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $status,
            'due_at' => $dueIso,
            'created_at' => $createdIso,
            'updated_at' => $updatedIso,
        ];
    }
}
