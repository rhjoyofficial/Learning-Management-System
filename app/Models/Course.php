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

    public function isAccessibleNow(): bool
    {
        $now = now();

        if ($this->start_at && $now->lt($this->start_at)) {
            return false;
        }

        if ($this->end_at && $now->gt($this->end_at)) {
            return false;
        }

        return true;
    }

    public function hasDemoVideo(): bool
    {
        return (bool) $this->demo_video_url;
    }
}
