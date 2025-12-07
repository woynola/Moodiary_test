<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class ForumComment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'content',
        'is_anonymous',
    ];

    protected function casts(): array
    {
        return [
            'is_anonymous' => 'boolean',
        ];
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(ForumPost::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(ForumComment::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(ForumComment::class, 'parent_id');
    }

    public function reactions(): MorphMany
    {
        return $this->morphMany(ForumReaction::class, 'reactable');
    }

    public function reports(): MorphMany
    {
        return $this->morphMany(ForumReport::class, 'reportable');
    }

    public function scopeTopLevel($query)
    {
        return $query->whereNull('parent_id');
    }

    public function getAuthorName(): string
    {
        return $this->is_anonymous ? 'Anonymous' : $this->user->name;
    }

    public function getTotalReactions(): int
    {
        return $this->likes_count + $this->supports_count + $this->hugs_count;
    }
}
