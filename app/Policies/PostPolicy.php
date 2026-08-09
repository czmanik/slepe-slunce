<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Post;
use App\Models\User;

class PostPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, Post $post): bool { return $user->canPublish() || $post->created_by === $user->id; }
    public function create(User $user): bool { return $user->is_active; }
    public function update(User $user, Post $post): bool { return $user->canPublish() || $post->created_by === $user->id; }
    public function delete(User $user, Post $post): bool { return $user->role === UserRole::Admin || ($user->role === UserRole::Editor && $post->created_by === $user->id); }
    public function deleteAny(User $user): bool { return $user->role === UserRole::Admin; }
    public function restore(User $user, Post $post): bool { return $user->role === UserRole::Admin; }
    public function forceDelete(User $user, Post $post): bool { return $user->role === UserRole::Admin; }
}
