<?php

namespace App\Policies;

use App\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    /**
     * Create a new policy instance.
     *
     */
    public function __construct()
    {
        //
    }

    public function before(User $currentUser, $ability)
    {
        if ($currentUser->isSuperAdmin()) {
            return true;
        }
    }

    public function read(User $currentUser)
    {
        return true;
    }

    public function create()
    {
        return false;
    }

    public function delete()
    {
        return false;
    }

    public function update(User $currentUser, User $userToUpdate)
    {
//        return $currentUser->isSuperAdmin();
        return $currentUser->id === $userToUpdate->id;
    }
}
