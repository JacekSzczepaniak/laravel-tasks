<?php

namespace Database\Factories;

use App\Domain\Tasks\Enum\TaskStatus;
use App\Infrastructure\Tasks\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition(): array {
        return [
            'user_id' => User::factory(),
            'title' => $this->faker->sentence(4),
            'description' => $this->faker->optional()->paragraph(),
            'status' => $this->faker->randomElement([TaskStatus::Todo, TaskStatus::InProgress, TaskStatus::Done]),
//            'owner_id' => User::factory(),
            'due_at' => $this->faker->optional()->dateTimeBetween('now', '+2 months'),
        ];
    }
}
