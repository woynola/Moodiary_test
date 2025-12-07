<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Journal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'notebook_id',
        'title',
        'content',
        'excerpt',
        'entry_date',
        'is_private',
        'pin_code',
        'mood_score',
        'weather',
        'reading_time',
        'is_published',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'entry_date' => 'date',
            'published_at' => 'datetime',
            'is_private' => 'boolean',
            'is_published' => 'boolean',
        ];
    }

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function notebook(): BelongsTo
    {
        return $this->belongsTo(Notebook::class);
    }

    public function media(): HasMany
    {
        return $this->hasMany(JournalMedia::class);
    }

    public function tags(): HasMany
    {
        return $this->hasMany(JournalTag::class);
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('entry_date', $date);
    }

    public function scopeByMonth($query, $year, $month)
    {
        return $query->whereYear('entry_date', $year)
                     ->whereMonth('entry_date', $month);
    }

    // Methods
    public function isLocked(): bool
    {
        return !is_null($this->pin_code);
    }

    public function verifyPin($pin): bool
    {
        return $this->pin_code === hash('sha256', $pin);
    }

    public function setPin($pin): void
    {
        $this->pin_code = hash('sha256', $pin);
        $this->save();
    }

    public function removePin(): void
    {
        $this->pin_code = null;
        $this->save();
    }

    public function calculateReadingTime(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, ceil($wordCount / 200));
    }

    public function generateExcerpt(): string
    {
        $text = strip_tags($this->content);
        return substr($text, 0, 150) . '...';
    }
}
