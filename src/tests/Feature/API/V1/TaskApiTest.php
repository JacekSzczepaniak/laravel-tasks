<?php

use App\Infrastructure\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use function Pest\Laravel\{postJson, getJson, putJson, deleteJson};

uses(RefreshDatabase::class)->group('api');

beforeEach(function () {
    config(['sanctum.expiration' => null]);
});


it('owner can create a task', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $res = postJson('/api/v1/tasks', [
        'title' => 'My Task',
        'status' => 'todo',
        'due_at' => now()->addDay()->toISOString(),
    ]);

    $res->assertCreated()
        ->assertJsonPath('data.title', 'My Task')
        ->assertJsonPath('data.status', 'todo');
});

it('observer can view but cannot update', function () {
    [$owner, $observer] = User::factory()->count(2)->create();

    Sanctum::actingAs($owner);
    $task = Task::factory()->for($owner, 'owner')->create();
    $task->observers()->attach($observer->id);

    Sanctum::actingAs($observer);
    // Może odczytać
    getJson("/api/v1/tasks/{$task->id}")->assertOk();
    // Nie może modyfikować
    putJson("/api/v1/tasks/{$task->id}", ['title' => 'X'])->assertForbidden();

    // Sprawdzamy czy tytuł nie został zmieniony
    expect($task->fresh()->title)->not->toBe('Changed Title');

});

it('owner can assign and remove observer', function () {
    [$owner, $observer] = User::factory()->count(2)->create();
    Sanctum::actingAs($owner);
    $task = Task::factory()->for($owner, 'owner')->create();

    // Dodawanie obserwatora
    postJson("/api/v1/tasks/{$task->id}/observers/{$observer->id}")
        ->assertNoContent();

    expect($task->observers)->toHaveCount(1)
        ->first()->id->toBe($observer->id);

    // Usuwanie obserwatora
    deleteJson("/api/v1/tasks/{$task->id}/observers/{$observer->id}")
        ->assertNoContent();

    expect($task->fresh()->observers)->toHaveCount(0);
});

it('lists only tasks user has access to', function () {
    [$user, $other] = User::factory()->count(2)->create();
    Sanctum::actingAs($user);

    // Zadania użytkownika
    Task::factory(2)->for($user, 'owner')->create();

    // Zadania, które obserwuje
    $observedTask = Task::factory()->for($other, 'owner')->create();
    $observedTask->observers()->attach($user->id);

    // Zadania innych użytkowników
    Task::factory(3)->for($other, 'owner')->create();

    $response = getJson('/api/v1/tasks');

    $response->assertOk()
        ->assertJsonCount(3, 'data') // 2 własne + 1 obserwowane
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'title',
                    'status',
                    'due_at',
                    'owner_id'
                ]
            ],
            'meta' => [
                'current_page',
                'last_page',
                'per_page',
                'total'
            ]
        ]);
});

test('task status transitions are validated', function () {
    $user = User::factory()->create();
    Sanctum::actingAs($user);

    $task = Task::factory()
        ->for($user, 'owner')
        ->create(['status' => 'todo']);

    // Prawidłowa zmiana statusu
    putJson("/api/v1/tasks/{$task->id}", [
        'status' => 'in_progress'
    ])->assertOk();

    // Nieprawidłowa zmiana statusu
    putJson("/api/v1/tasks/{$task->id}", [
        'status' => 'invalid_status'
    ])->assertUnprocessable()
        ->assertJsonValidationErrors(['status']);
});
