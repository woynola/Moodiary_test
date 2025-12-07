<?php

namespace App\Policies;

use App\Models\UserChallenge;
use App\Models\User;

class UserChallengePolicy
{
    public function update(User $user, UserChallenge $userChallenge): bool
    {
        return $user->id === $userChallenge->user_id;
    }

    public function delete(User $user, UserChallenge $userChallenge): bool
    {
        return $user->id === $userChallenge->user_id;
    }
}
