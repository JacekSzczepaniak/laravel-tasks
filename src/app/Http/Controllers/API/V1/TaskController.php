<?php

namespace App\Http\Controllers\API\V1;

use App\Application\Tasks\{AssignObserver, CreateTask, DeleteTask, ListTasksForUser, RemoveObserver, UpdateTask};
use App\Domain\Tasks\Enum\TaskStatus;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use OpenApi\Annotations as OA;
use App\Http\Requests\Tasks\{StoreTaskRequest, UpdateTaskRequest};
use App\Http\Resources\V1\TaskResource;
use App\Infrastructure\Tasks\Models\Task;
use Illuminate\Http\Request;

/**
 * @OA\Info(
 *   title="Tasks API",
 *   version="1.0.0",
 *   description="API do zarządzania zadaniami (owner + obserwatorzy)"
 * )
 *
 * @OA\Server(
 *   url="/api",
 *   description="Local"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="sanctum",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT",
 *   description="Bearer {token}"
 * )
 *
 * @OA\Tag(
 *   name="Tasks",
 *   description="Operacje na zadaniach"
 * )
 */
class TaskController extends Controller
{
    use AuthorizesRequests;

    /**
     * @OA\Get(
     *   path="/v1/tasks",
     *   tags={"Tasks"},
     *   security={{"sanctum": {}}},
     *   summary="Lista zadań",
     *   @OA\Response(response=200, description="OK", @OA\JsonContent(ref="#/components/schemas/TaskList")),
     *   @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function index(Request $req, ListTasksForUser $query): AnonymousResourceCollection
    {
        $perPage = (int) $req->query('per_page', 15);
        $page    = (int) $req->query('page', 1);

        $filters = $req->only(['status', 'due_from', 'due_to', 'sort', 'scope']);
        $paginator = $query->paginate($req->user()->id, $filters, $perPage, $page);

        return TaskResource::collection(collect($paginator->items()))
            ->additional([
                'meta' => [
                    'current_page' => $paginator->currentPage(),
                    'per_page'     => $paginator->perPage(),
                    'total'        => $paginator->total(),
                    'last_page'    => $paginator->lastPage(),
                ],
            ]);
    }

    public function store(StoreTaskRequest $req, CreateTask $create): JsonResponse
    {
        $e = $create(
            $req->user()->id,
            $req->input('title'),
            $req->input('description'),
            $req->filled('status') ? TaskStatus::from($req->input('status')) : null,
            $req->input('due_at')
        );
        return (new TaskResource($e))->response()->setStatusCode(201);
    }

    /**
     * @throws AuthorizationException
     */
    public function show(Request $req, Task $task): TaskResource
    {
        $this->authorize('view', $task);
        return new TaskResource($task);
    }

    /**
     * @throws AuthorizationException
     */
    public function update(UpdateTaskRequest $req, Task $task, UpdateTask $update): TaskResource
    {
        $this->authorize('update', $task);
        $e = $update(
            $task->id,
            $req->user()->id,
            $req->input('title', $task->title),
            $req->has('description') ? $req->input('description') : null,
            $req->has('status') ? TaskStatus::from($req->input('status')) : null,
            $req->has('due_at') ? $req->input('due_at') : null
        );
        return new TaskResource($e);
    }

    /**
     * @throws AuthorizationException
     */
    public function destroy(Request $req, Task $task, DeleteTask $delete): Response
    {
        $this->authorize('delete', $task);
        $delete($task->id, $req->user()->id);
        return response()->noContent();
    }

    /**
     * @throws AuthorizationException
     */
    public function attachObserver(Request $req, Task $task, int $user, AssignObserver $attach): Response
    {
        $this->authorize('attachObserver', $task);
        $attach($task->id, $user, $req->user()->id);
        return response()->noContent();
    }

    /**
     * @throws AuthorizationException
     */
    public function detachObserver(Request $req, Task $task, int $user, RemoveObserver $detach): Response
    {
        $this->authorize('detachObserver', $task);
        $detach($task->id, $user, $req->user()->id);
        return response()->noContent();
    }
}
