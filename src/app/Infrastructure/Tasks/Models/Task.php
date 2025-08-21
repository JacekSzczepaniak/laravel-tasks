<?php

namespace App\Infrastructure\Tasks\Models;

use App\Domain\Tasks\Enum\TaskStatus;
use App\Models\User;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


/**
 * @method static TaskFactory factory(...$parameters)
 * @use HasFactory<TaskFactory>
 */
class Task extends Model
{
    /** @phpstan-ignore-next-line missingType.generics: Larastan nie zawsze poprawnie wykrywa typ fabryki z newFactory() */
    use HasFactory;

    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }

    protected $fillable = ['user_id','title','description','status','due_at'];

    protected $casts = [
        'due_at' => 'immutable_datetime',
        'status' => TaskStatus::class,
    ];

    /**
     * @return BelongsTo<User, Task>
     */
    public function owner(): BelongsTo
    {
        /** @var BelongsTo<User, Task> $rel */
        $rel = $this->belongsTo(User::class, 'user_id');
        return $rel;
    }

    /**
     * @return BelongsToMany<User, Task>
     */
    public function observers(): BelongsToMany
    {
        /** @var BelongsToMany<User, Task> $rel */
        $rel = $this->belongsToMany(User::class, 'task_observers')->withTimestamps();
        return $rel;
    }
}

