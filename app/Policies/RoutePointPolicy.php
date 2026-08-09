<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\RoutePoint;
use App\Models\User;

class RoutePointPolicy
{
    public function viewAny(User $user): bool { return $user->canPublish(); }
    public function view(User $user, RoutePoint $routePoint): bool { return $user->canPublish(); }
    public function create(User $user): bool { return $user->canPublish(); }
    public function update(User $user, RoutePoint $routePoint): bool { return $user->canPublish(); }
    public function delete(User $user, RoutePoint $routePoint): bool { return $user->role === UserRole::Admin; }
    public function deleteAny(User $user): bool { return $user->role === UserRole::Admin; }
}
