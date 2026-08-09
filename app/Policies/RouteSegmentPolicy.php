<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\RouteSegment;
use App\Models\User;

class RouteSegmentPolicy
{
    public function viewAny(User $user): bool { return $user->canPublish(); }
    public function view(User $user, RouteSegment $routeSegment): bool { return $user->canPublish(); }
    public function create(User $user): bool { return $user->canPublish(); }
    public function update(User $user, RouteSegment $routeSegment): bool { return $user->canPublish(); }
    public function delete(User $user, RouteSegment $routeSegment): bool { return $user->role === UserRole::Admin; }
    public function deleteAny(User $user): bool { return $user->role === UserRole::Admin; }
}
