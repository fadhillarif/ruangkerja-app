<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    protected $fillable = ['department_id', 'owner_id', 'name', 'description', 'status', 'priority', 'due_at'];

    protected function casts(): array
    {
        return ['due_at' => 'date'];
    }

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function owner(): BelongsTo { return $this->belongsTo(User::class, 'owner_id'); }
    public function tasks(): HasMany { return $this->hasMany(Task::class); }
}
