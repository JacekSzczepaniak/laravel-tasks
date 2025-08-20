<?php

use App\Domain\Tasks\Enum\TaskStatus;
use App\Infrastructure\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;
uses(RefreshDatabase::class)->group('api');

it('paginates and filters tasks', function () {
    $owner = User::factory()->create();
    $observer = User::factory()->create();
    Sanctum::actingAs($owner);

    // 6 zadań ownera
    Task::factory()->count(3)->create(['user_id' => $owner->id, 'status' => TaskStatus::Todo]);
    Task::factory()->count(3)->create(['user_id' => $owner->id, 'status' => TaskStatus::Done]);

    // 2 obserwowane (dla innego ownera)
    $otherOwner = User::factory()->create();
    $observed = Task::factory()->count(2)->create(['user_id' => $otherOwner->id, 'status' => TaskStatus::InProgress]);
    foreach ($observed as $t) $t->observers()->attach($owner->id);

    // filtr status + paginacja po 2
    $res = getJson('/api/v1/tasks?status=todo&per_page=2&page=1&sort=due_at,-created_at');

    $res->assertOk()
        ->assertJsonStructure([
            'data' => [['id','title','status','due_at']],
            'meta' => ['current_page','per_page','total','last_page']
        ])
        ->assertJsonPath('meta.per_page', 2)
        ->assertJsonPath('meta.current_page', 1);
});

it('filters by scope observed', function () {
    $owner = User::factory()->create();
    Sanctum::actingAs($owner);

    // owned
    Task::factory()->count(2)->create(['user_id' => $owner->id, 'status' => TaskStatus::Todo]);

    // observed
    $other = User::factory()->create();
    $obs = Task::factory()->count(3)->create(['user_id' => $other->id, 'status' => TaskStatus::InProgress]);
    foreach ($obs as $t) $t->observers()->attach($owner->id);

    $res = getJson('/api/v1/tasks?scope=observed');

    $res->assertOk();
    // szybka kontrola: wszystkie powinny mieć status in_progress (po naszym setupie)
    collect($res->json('data'))->each(fn($row) => expect($row['status'])->toBe('in_progress'));
});
