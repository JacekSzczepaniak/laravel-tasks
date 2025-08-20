<?php

namespace App\Infrastructure\Tasks\Repositories;

use App\Domain\Tasks\Entities\TaskEntity;
use App\Domain\Tasks\Repositories\TaskRepository;
use App\Infrastructure\Tasks\Models\Task;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

final class EloquentTaskRepository implements TaskRepository
{
    private function mapToEntity(Task $m): TaskEntity {
        return new TaskEntity(
            id: $m->id,
            ownerId: $m->user_id,
            title: $m->title,
            description: $m->description,
            status: $m->status,
            dueAt: $m->due_at ? CarbonImmutable::parse($m->due_at) : null,
            createdAt: $m->created_at?->toDateTimeImmutable(),
            updatedAt: $m->updated_at?->toDateTimeImmutable(),
        );
    }

    private function fillModel(TaskEntity $e, ?Task $m = null): Task {
        $m ??= new Task();
        $m->user_id = $e->ownerId;
        $m->title = $e->title;
        $m->description = $e->description;
        $m->status = $e->status;
        $m->due_at = $e->dueAt?->toDateTimeString();
        return $m;
    }

    public function findForView(int $taskId, int $requesterId): ?TaskEntity {
        $m = Task::query()
            ->whereKey($taskId)
            ->where(function ($q) use ($requesterId) {
                $q->where('user_id', $requesterId)
                    ->orWhereHas('observers', fn($oq)=>$oq->whereKey($requesterId));
            })
            ->first();

        return $m ? $this->mapToEntity($m) : null;
    }

    public function save(TaskEntity $task): TaskEntity {
        $model = $task->id ? Task::findOrFail($task->id) : null;
        $model = $this->fillModel($task, $model);
        $model->save();
        return $this->mapToEntity($model);
    }

    public function delete(int $taskId, int $requesterId): void {
        $m = Task::whereKey($taskId)->where('user_id', $requesterId)->firstOrFail();
        $m->delete();
    }

    public function listForUser(int $userId, array $filters = []): array {
        $q = Task::query()
            ->where('user_id', $userId)
            ->orWhereHas('observers', fn($oq)=>$oq->whereKey($userId));

        if (!empty($filters['status'])) $q->where('status', $filters['status']);
        if (!empty($filters['due_from'])) $q->where('due_at', '>=', $filters['due_from']);
        if (!empty($filters['due_to']))   $q->where('due_at', '<=', $filters['due_to']);

        return $q->latest('due_at')->get()->map(fn($m)=>$this->mapToEntity($m))->all();
    }

    public function assignObserver(int $taskId, int $observerId, int $requesterId): void {
        $m = Task::whereKey($taskId)->where('user_id', $requesterId)->firstOrFail();
        $m->observers()->syncWithoutDetaching($observerId);
    }

    public function removeObserver(int $taskId, int $observerId, int $requesterId): void {
        $m = Task::whereKey($taskId)->where('user_id', $requesterId)->firstOrFail();
        $m->observers()->detach($observerId);
    }


    public function paginateForUser(int $userId, array $filters = [], int $perPage = 15, int $page = 1): LengthAwarePaginator
    {
        $q = Task::query();

        $scope = $filters['scope'] ?? 'all';
// scope: owned | observed | all

        if ($scope === 'owned') {
            $q->where('user_id', $userId);
        } elseif ($scope === 'observed') {
            $q->whereHas('observers', fn ($oq) => $oq->whereKey($userId));
        } else { // all
            $q->where(function ($qq) use ($userId) {
                $qq->where('user_id', $userId)
                    ->orWhereHas('observers', fn ($oq) => $oq->whereKey($userId));
            });
        }

        if (!empty($filters['status'])) {
            $q->where('status', $filters['status']);
        }
        if (!empty($filters['due_from'])) {
            $q->where('due_at', '>=', $filters['due_from']);
        }
        if (!empty($filters['due_to'])) {
            $q->where('due_at', '<=', $filters['due_to']);
        }

        // sort=due_at,-created_at
        if (!empty($filters['sort'])) {
            $sorts = is_array($filters['sort']) ? $filters['sort'] : explode(',', $filters['sort']);
            foreach ($sorts as $s) {
                $s = trim($s);
                $dir = Str::startsWith($s, '-') ? 'desc' : 'asc';
                $col = ltrim($s, '-');
                if (in_array($col, ['title','status','due_at','created_at','updated_at'], true)) {
                    $q->orderBy($col, $dir);
                }
            }
        } else {
            $q->latest('due_at');
        }

        if (!empty($filters['q'])) {
            $term = trim((string) $filters['q']);
            if ($term !== '') {
                // prosty LIKE z ucieczką % i _
                $termEsc = str_replace(['%', '_'], ['\%','\_'], $term);
                $like = '%'.$termEsc.'%';

                $q->where(function (Builder $qq) use ($like) {
                    $qq->where('title', 'like', $like)
                        ->orWhere('description', 'like', $like);
                });
            }
        }

        $paginator = $q->paginate($perPage, ['*'], 'page', $page);

        $paginator->setCollection(
            $paginator->getCollection()->map(fn($m) => $this->mapToEntity($m))
        );

        return $paginator;
    }

    public function getObserverIds(int $taskId, int $requesterId): array
    {
        $task = Task::whereKey($taskId)
            ->where('user_id', $requesterId)
            ->firstOrFail();

        return $task->observers()->pluck('users.id')->all();
    }

}
