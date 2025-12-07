<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ForumPost extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'content',
        'excerpt',
        'is_anonymous',
        'is_pinned',
        'is_locked',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
            'is_pinned' => 'boolean',
            'is_locked' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ForumCategory::class, 'category_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(ForumComment::class, 'post_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(ForumPostMedia::class, 'post_id');
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(ForumReaction::class, 'reactable');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(ForumReport::class, 'reportable');
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeNotLocked($query)
    {
        return $query->where('is_locked', false);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }

    public function incrementViews(): void
    {
        $this->increment('views');
    }

    public function getAuthorName(): string
    {
        return $this->is_anonymous ? 'Anonymous' : $this->user->name;
    }
}
