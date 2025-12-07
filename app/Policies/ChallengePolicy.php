<?php

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;

class ChallengePolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Challenge $challenge): bool
    {
        return $user->id === $challenge->created_by || $user->is_admin;
    }

    public function delete(User $user, Challenge $challenge): bool
    {
        return $user->id === $challenge->created_by || $user->is_admin;
    }
}
