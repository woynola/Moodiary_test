<?php

namespace App\Policies;

use App\Models\ForumPost;
use App\Models\User;

class ForumPostPolicy
{
    public function view(User $user, ForumPost $post): bool
    {
        return !$post->is_locked;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, ForumPost $post): bool
    {
        return $user->id === $post->user_id || $user->canModerate();
    }

    public function delete(User $user, ForumPost $post): bool
    {
        return $user->id === $post->user_id || $user->canModerate();
    }
}
