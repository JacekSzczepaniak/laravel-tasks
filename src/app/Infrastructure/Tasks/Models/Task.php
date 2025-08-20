<?php

namespace App\Infrastructure\Tasks\Models;

use App\Domain\Tasks\Enum\TaskStatus;
use App\Models\User;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Task extends Model
{
    use HasFactory;

    protected static function newFactory(): TaskFactory
    {
        return TaskFactory::new();
    }

    protected $fillable = ['user_id','title','description','status','due_at'];

    protected $casts = [
        'due_at' => 'datetime',
        'status' => TaskStatus::class,
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function observers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_observers')->withTimestamps();
    }
}
