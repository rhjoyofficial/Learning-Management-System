<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    use HasFactory;

    protected $fillable = [
        'module_id',
        'title',
        'video_url',
        'duration',
        'is_free',
        'position',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'is_free' => 'boolean',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function module(): BelongsTo
    {
        return $this->belongsTo(Module::class);
    }

    public function progress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function isAccessibleNow(): bool
    {
        $now = now();

        // If no start_at → accessible
        if (!$this->start_at) {
            return true;
        }

        $hasStarted = $now->greaterThanOrEqualTo($this->start_at);

        if (!$this->end_at) {
            return $hasStarted;
        }

        $hasNotEnded = $now->lessThanOrEqualTo($this->end_at);

        return $hasStarted && $hasNotEnded;
    }
}
