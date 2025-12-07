<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Model;

class Challenge extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
        'duration_days',
        'rules',
        'is_template',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_template' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function userChallenges(): HasMany
    {
        return $this->hasMany(UserChallenge::class);
    }

    public function rewards(): HasMany
    {
        return $this->hasMany(ChallengeReward::class);
    }

    public function scopeTemplates($query)
    {
        return $query->where('is_template', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
