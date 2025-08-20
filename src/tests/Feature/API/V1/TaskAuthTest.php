<?php

use App\Infrastructure\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\{getJson, postJson, putJson, deleteJson};

uses(RefreshDatabase::class)->group('api', 'auth');

/**
 * ─────────────────────────────────────────────────────────────────────────────
 * 401 – gość nie ma dostępu do API
 * ─────────────────────────────────────────────────────────────────────────────
 */
it('rejects guest access with 401', function () {
    getJson('/api/v1/tasks')->assertStatus(401);
    postJson('/api/v1/tasks', ['title' => 'X'])->assertStatus(401);
});

/**
 * ─────────────────────────────────────────────────────────────────────────────
 * 403 – brak uprawnień (nie-owner) do modyfikacji i podglądu cudzych zadań
 * ─────────────────────────────────────────────────────────────────────────────
 */
it('non-owner cannot update or delete someone else task (403)', function () {
    [$owner, $stranger] = User::factory()->count(2)->create();
    $task = Task::factory()->for($owner, 'owner')->create();

    Sanctum::actingAs($stranger);

    putJson("/api/v1/tasks/{$task->id}", ['title' => 'Y'])->assertStatus(403);
    deleteJson("/api/v1/tasks/{$task->id}")->assertStatus(403);
});

it('non-owner non-observer cannot view someone else task (403)', function () {
    [$owner, $stranger] = User::factory()->count(2)->create();
    $task = Task::factory()->for($owner, 'owner')->create();

    Sanctum::actingAs($stranger);
    getJson("/api/v1/tasks/{$task->id}")->assertStatus(403);
});

/**
 * ─────────────────────────────────────────────────────────────────────────────
 * 403 – obserwator widzi, ale nie modyfikuje
 * ─────────────────────────────────────────────────────────────────────────────
 */
it('observer can view but cannot update or delete (403 on modify)', function () {
    [$owner, $observer] = User::factory()->count(2)->create();
    $task = Task::factory()->for($owner, 'owner')->create();
    $task->observers()->attach($observer->id);

    Sanctum::actingAs($observer);

    getJson("/api/v1/tasks/{$task->id}")->assertOk();
    putJson("/api/v1/tasks/{$task->id}", ['title' => 'Z'])->assertStatus(403);
    deleteJson("/api/v1/tasks/{$task->id}")->assertStatus(403);
});

/**
 * ─────────────────────────────────────────────────────────────────────────────
 * 404 – brak zasobu (nieistniejące ID)
 * ─────────────────────────────────────────────────────────────────────────────
 */
it('returns 404 for non-existing task id on show/update/delete', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    getJson('/api/v1/tasks/999999')->assertStatus(404);
    putJson('/api/v1/tasks/999999', ['title' => 'Y'])->assertStatus(404);
    deleteJson('/api/v1/tasks/999999')->assertStatus(404);
});

/**
 * ─────────────────────────────────────────────────────────────────────────────
 * 403 – tylko owner może zarządzać obserwatorami
 * ─────────────────────────────────────────────────────────────────────────────
 */
it('non-owner cannot attach or detach observers', function () {
    [$owner, $stranger, $someone] = User::factory()->count(3)->create();
    $task = Task::factory()->for($owner, 'owner')->create();

    Sanctum::actingAs($stranger);

    postJson("/api/v1/tasks/{$task->id}/observers/{$someone->id}")->assertStatus(403);
    deleteJson("/api/v1/tasks/{$task->id}/observers/{$someone->id}")->assertStatus(403);
});

/**
 * ─────────────────────────────────────────────────────────────────────────────
 * Idempotencja: podwójne dodanie obserwatora nie duplikuje wpisu; usunięcie
 * niepodpiętego obserwatora zwraca 204 (no-op)
 * ─────────────────────────────────────────────────────────────────────────────
 */
it('attach observer is idempotent; detach non-attached returns 204', function () {
    [$owner, $obs] = User::factory()->count(2)->create();
    $task = Task::factory()->for($owner, 'owner')->create();

    Sanctum::actingAs($owner);

    // pierwsze dodanie
    postJson("/api/v1/tasks/{$task->id}/observers/{$obs->id}")->assertNoContent();
    expect($task->observers()->count())->toBe(1);

    // drugie dodanie (idempotent)
    postJson("/api/v1/tasks/{$task->id}/observers/{$obs->id}")->assertNoContent();
    $task->refresh();
    expect($task->observers()->count())->toBe(1);

    // odpięcie istniejącego
    deleteJson("/api/v1/tasks/{$task->id}/observers/{$obs->id}")->assertNoContent();
    $task->refresh();
    expect($task->observers()->count())->toBe(0);

    // odpięcie nieistniejącego (no-op 204)
    deleteJson("/api/v1/tasks/{$task->id}/observers/{$obs->id}")->assertNoContent();
});
