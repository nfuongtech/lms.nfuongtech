<?php

namespace App\Policies;

use App\Models\User;
use App\Models\LichHoc;
use Illuminate\Auth\Access\HandlesAuthorization;

class LichHocPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_any_lich::hoc');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LichHoc $lichHoc): bool
    {
        return $user->can('view_lich::hoc');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create_lich::hoc');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LichHoc $lichHoc): bool
    {
        return $user->can('update_lich::hoc');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LichHoc $lichHoc): bool
    {
        return $user->can('delete_lich::hoc');
    }

    /**
     * Determine whether the user can bulk delete.
     */
    public function deleteAny(User $user): bool
    {
        return $user->can('delete_any_lich::hoc');
    }

    /**
     * Determine whether the user can permanently delete.
     */
    public function forceDelete(User $user, LichHoc $lichHoc): bool
    {
        return $user->can('force_delete_lich::hoc');
    }

    /**
     * Determine whether the user can permanently bulk delete.
     */
    public function forceDeleteAny(User $user): bool
    {
        return $user->can('force_delete_any_lich::hoc');
    }

    /**
     * Determine whether the user can restore.
     */
    public function restore(User $user, LichHoc $lichHoc): bool
    {
        return $user->can('restore_lich::hoc');
    }

    /**
     * Determine whether the user can bulk restore.
     */
    public function restoreAny(User $user): bool
    {
        return $user->can('restore_any_lich::hoc');
    }

    /**
     * Determine whether the user can replicate.
     */
    public function replicate(User $user, LichHoc $lichHoc): bool
    {
        return $user->can('replicate_lich::hoc');
    }

    /**
     * Determine whether the user can reorder.
     */
    public function reorder(User $user): bool
    {
        return $user->can('reorder_lich::hoc');
    }
}
