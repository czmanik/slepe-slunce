<?php
namespace App\Policies;
use App\Models\MapPhoto;
use App\Models\User;
class MapPhotoPolicy
{
    public function viewAny(User $user): bool { return true; }
    public function view(User $user, MapPhoto $photo): bool { return true; }
    public function create(User $user): bool { return true; }
    public function update(User $user, MapPhoto $photo): bool { return $user->canPublish() || $photo->user_id === $user->id; }
    public function delete(User $user, MapPhoto $photo): bool { return $user->canPublish() || $photo->user_id === $user->id; }
    public function deleteAny(User $user): bool { return $user->canPublish(); }
}
