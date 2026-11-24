<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    protected $fillable = ['project_id', 'assignee_id', 'created_by', 'title', 'description', 'status', 'priority', 'due_at', 'position'];

    protected function casts(): array
    {
        return ['due_at' => 'date'];
    }

    public function project(): BelongsTo { return $this->belongsTo(Project::class); }
    public function assignee(): BelongsTo { return $this->belongsTo(User::class, 'assignee_id'); }
}
