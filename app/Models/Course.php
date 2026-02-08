<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'instructor_id',
        'category_id',
        'title',
        'slug',
        'description',
        'image',
        'duration',
        'price',
        'offer_price',
        'is_paid',
        'level',
        'thumbnail',
        'demo_video_url',
        'note',
        'promo_text',
        'status',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'price' => 'decimal:2',
        'offer_price' => 'decimal:2',
    ];

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(Module::class)->orderBy('position');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Check if the course is currently accessible based on start_at and end_at
     */
    public function isAccessibleNow(): bool
    {
        $now = now();

        // If no start_at is set, course is accessible
        if (!$this->start_at) {
            return true;
        }

        // Check if current time is after start_at
        $hasStarted = $now->greaterThanOrEqualTo($this->start_at);

        // If no end_at is set, only check start_at
        if (!$this->end_at) {
            return $hasStarted;
        }

        // Check if current time is before end_at
        $hasNotEnded = $now->lessThanOrEqualTo($this->end_at);

        return $hasStarted && $hasNotEnded;
    }

    /**
     * Check if course hasn't started yet
     */
    public function isUpcoming(): bool
    {
        if (!$this->start_at) {
            return false;
        }

        return now()->lessThan($this->start_at);
    }

    /**
     * Check if course has ended
     */
    public function hasEnded(): bool
    {
        if (!$this->end_at) {
            return false;
        }

        return now()->greaterThan($this->end_at);
    }

    /**
     * Get seconds until course starts (0 if already started)
     */
    public function getSecondsUntilStart(): int
    {
        if (!$this->start_at || !$this->isUpcoming()) {
            return 0;
        }

        return now()->diffInSeconds($this->start_at, false);
    }

    public function hasDemoVideo(): bool
    {
        return (bool) $this->demo_video_url;
    }
}
