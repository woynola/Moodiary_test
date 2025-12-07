<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'username',
        'password',
        'avatar_url',
        'bio',
        'theme',
        'google_id',
        'google_token',
        'google_refresh_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
        'google_refresh_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_moderator' => 'boolean',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    // Relationships
    public function journals(): HasMany
    {
        return $this->hasMany(Journal::class);
    }

    public function notebooks(): HasMany
    {
        return $this->hasMany(Notebook::class);
    }

    public function moods(): HasMany
    {
        return $this->hasMany(Mood::class);
    }

    public function moodInsights(): HasMany
    {
        return $this->hasMany(MoodInsight::class);
    }

    public function forumPosts(): HasMany
    {
        return $this->hasMany(ForumPost::class);
    }

    public function forumComments(): HasMany
    {
        return $this->hasMany(ForumComment::class);
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(UserChallenge::class);
    }

    public function badges(): HasMany
    {
        return $this->hasMany(UserBadge::class);
    }

    public function level(): HasOne
    {
        return $this->hasOne(UserLevel::class);
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function createdChallenges(): HasMany
    {
        return $this->hasMany(Challenge::class, 'created_by');
    }

    public function forumReports(): HasMany
    {
        return $this->hasMany(ForumReport::class);
    }

    public function reviewedReports(): HasMany
    {
        return $this->hasMany(ForumReport::class, 'reviewed_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('is_admin', true);
    }

    public function scopeModerators($query)
    {
        return $query->where('is_moderator', true);
    }

    // Methods
    public function isAdmin(): bool
    {
        return $this->is_admin;
    }

    public function isModerator(): bool
    {
        return $this->is_moderator;
    }

    public function canModerate(): bool
    {
        return $this->is_admin || $this->is_moderator;
    }

    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    public function getAvatarUrl(): string
    {
        return $this->avatar_url ?? asset('images/default-avatar.png');
    }
}
