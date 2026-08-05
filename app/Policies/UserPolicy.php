<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isAdmin();
    }

    public function view(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function update(User $user, User $target): bool
    {
        return $user->isAdmin();
    }

    /**
     * Deaktivace účtu; admin nemůže deaktivovat sám sebe.
     */
    public function deactivate(User $user, User $target): bool
    {
        return $user->isAdmin() && $user->id !== $target->id;
    }
}
