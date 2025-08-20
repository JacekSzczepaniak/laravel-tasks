<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;
uses(RefreshDatabase::class)->group('api');

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create());
});

it('rejects missing title', function () {
    postJson('/api/v1/tasks', [
        'status' => 'todo',
    ])->assertStatus(422)->assertJsonValidationErrors(['title']);
});

it('rejects invalid status', function () {
    postJson('/api/v1/tasks', [
        'title' => 'X',
        'status' => 'invalid',
    ])->assertStatus(422)->assertJsonValidationErrors(['status']);
});
