<?php

namespace App\Policies;

use App\Models\Mood;
use App\Models\User;

class MoodPolicy
{
    public function view(User $user, Mood $mood): bool
    {
        return $user->id === $mood->user_id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Mood $mood): bool
    {
        return $user->id === $mood->user_id;
    }

    public function delete(User $user, Mood $mood): bool
    {
        return $user->id === $mood->user_id;
    }
}
