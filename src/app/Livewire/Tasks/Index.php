<?php

namespace App\Livewire\Tasks;

use App\Application\Tasks\AssignObserver;
use App\Application\Tasks\CreateTask;
use App\Application\Tasks\ListTasksForUser;
use App\Application\Tasks\RemoveObserver;
use App\Application\Tasks\UpdateTask;
use App\Application\Tasks\DeleteTask;
use App\Domain\Tasks\Enum\TaskStatus;
use App\Domain\Tasks\Repositories\TaskRepository;
use App\Infrastructure\Tasks\Models\Task as ETask;
use App\Infrastructure\Tasks\Repositories\EloquentTaskRepository;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination, AuthorizesRequests;

    private ListTasksForUser $listTasks;

    // Filtry/lista
    public string $scope = 'all'; // all|owned|observed
    public ?string $status = null; // todo|in_progress|done|null
    public int $perPage = 10;

    // Formularz create/edit
    public ?int $editingId = null;
    private ?int $editingOwnerId = null;
    public string $title = '';
    public ?string $description = null;
    public ?string $statusForm = 'todo';
    public ?string $due_at = null; // ISO datetime-local

    // Panel obserwatorów
    public ?int $observersTaskId = null;
    public string $observerSearch = '';
    public array $selectedObserverIds = [];
    public string $q = ''; // fraza wyszukiwania

    public function boot(ListTasksForUser $listTasks)
    {
        $this->listTasks = $listTasks;
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(array_map(fn($c) => $c->value, TaskStatus::cases()))],
            'due_at' => ['required', 'date'],
        ];
    }

    protected function messages(): array
    {
        return [
            'title.required' => 'Tytuł jest wymagany.',
            'status.in' => 'The selected status is invalid.',
            'due_at.required' => 'Termin jest wymagany.',
            'due_at.date' => 'Termin musi być poprawną datą i godziną.',
        ];
    }

    public function updating($name, $value): void
    {
        if (in_array($name, ['scope','status','perPage','q'])) {
            $this->resetPage();
        }
    }

    public function render(EloquentTaskRepository $repo)
    {
        $filters = [
            'scope' => $this->scope,
            'status' => $this->status,
            'q' => $this->q,
            'sort' => 'due_at,-created_at',
        ];

        $paginator = $repo->paginateForUser(
            auth()->id(),
            $filters,
            $this->perPage,
            page: (int) request()->get('page', $this->getPage())
        );

        return view('livewire.tasks.index', [
            'tasks' => $paginator,
            'statuses' => TaskStatus::cases(),
        ])->layout('layouts.app', ['title' => 'Tasks']);
    }

    /* ---------- Create / Edit ---------- */

    public function startCreate(): void
    {
        $this->resetForm();
        $this->dispatch('open-task-modal');
    }

    public function startEdit(int $taskId, TaskRepository $repo): void

    {
//        $task = ETask::findOrFail($taskId);
//
//        Gate::authorize('update', $task);
        $task = $repo->findForView($taskId, auth()->id());
        if (!$task) {
            abort(404);
        }


        $this->editingId = $task->id;
        $this->title = $task->title;
        $this->description = $task->description;
        $this->statusForm = $task->status->value ?? 'todo';
        $this->due_at = $task->dueAt?->format('Y-m-d\TH:i');

        $this->dispatch('open-task-modal');
    }

    public function save(CreateTask $create, UpdateTask $update): void
    {
        $this->validate();

        if ($this->editingId) {
            $status = $this->statusForm ? TaskStatus::tryFrom($this->statusForm) : null;
            $dueAt = $this->due_at ?: null;

            $update(
                id: $this->editingId,
                ownerId: (int) ($this->editingOwnerId ?? auth()->id()),
                title: $this->title,
                description: $this->description,
                status: $status,
                dueAt: $dueAt
            );
        } else {
            $payload = [
                'title' => $this->title,
                'description' => $this->description,
                'status' => $this->statusForm,
                'due_at' => $this->due_at ? Carbon::parse($this->due_at) : null,
            ];
            $create->handle(auth()->id(), $payload);
        }


        $this->resetForm();
        $this->dispatch('close-task-modal');
        $this->dispatch('toast', body: 'Saved');
    }

    public function delete(int $taskId, DeleteTask $delete): void
    {
        $delete($taskId, auth()->id());
        $this->dispatch('toast', body: 'Deleted');
        $this->resetPage();
    }

    protected function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->description = null;
        $this->statusForm = 'todo';
        $this->due_at = null;
    }

    /* ---------- Observers ---------- */

    public function openObservers(int $taskId, TaskRepository $repo): void
    {
        $entity = $repo->findForView($taskId, auth()->id());
        if (!$entity) {
            abort(404);
        }

        $observerIds = $repo->getObserverIds($taskId, auth()->id());

        $this->observersTaskId = $taskId;
        $this->selectedObserverIds = $observerIds;
        $this->observerSearch = '';

        $this->dispatch('open-observers-modal');
    }

    public function toggleObserver(int $userId, AssignObserver $attach, RemoveObserver $detach): void
    {
        if (!$this->observersTaskId) return;

        if (in_array($userId, $this->selectedObserverIds, true)) {
            $detach($this->observersTaskId, $userId, auth()->id());
            $this->selectedObserverIds = array_values(array_diff($this->selectedObserverIds, [$userId]));
        } else {
            // właściciela nie dodajemy jako obserwatora - spodziewamy się walidacji w use-case,
            // ale dla UX zatrzymujemy to również tutaj jeśli znamy ownera
            if ($userId === ($this->editingOwnerId ?? auth()->id())) {
                return;
            }
            $attach($this->observersTaskId, $userId, auth()->id());
            $this->selectedObserverIds[] = $userId;
        }
    }


    public function getObserverCandidatesProperty(): Collection|\Illuminate\Support\Collection
    {
        if (!$this->observersTaskId) return collect();

        $q = User::query()->whereKeyNot(auth()->id());

        if (trim($this->observerSearch) !== '') {
            $s = '%' . str_replace('%','', $this->observerSearch) . '%';
            $q->where(function($qq) use ($s) {
                $qq->where('name', 'like', $s)->orWhere('email', 'like', $s);
            });
        }

        return $q->limit(10)->get();
    }
}
