<?php

namespace Lunar\Policies;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use Lunar\Hub\Models\Staff;
use Illuminate\Auth\Access\Response;

class DefaultPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User|Staff $user = null): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User|Staff $user = null, Model $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User|Staff $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User|Staff $user, Model $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User|Staff $user, Model $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User|Staff $user, Model $model): bool
    {
        return true;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User|Staff $user, Model $model): bool
    {
        return true;
    }
}
