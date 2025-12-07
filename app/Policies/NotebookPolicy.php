<?php

namespace App\Policies;

use App\Models\Notebook;
use App\Models\User;

class NotebookPolicy
{
    public function update(User $user, Notebook $notebook): bool
    {
        return $user->id === $notebook->user_id;
    }

    public function delete(User $user, Notebook $notebook): bool
    {
        return $user->id === $notebook->user_id;
    }
}
