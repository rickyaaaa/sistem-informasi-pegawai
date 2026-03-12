<?php

namespace App\Policies;

use App\Models\Pegawai;
use App\Models\User;

class PegawaiPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return in_array($user->role, [User::ROLE_SUPER_ADMIN, User::ROLE_ADMIN_SATKER], true);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Pegawai $pegawai): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdminSatker() && $user->satker_id === $pegawai->satker_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        // Admin Satker must be attached to a satker (validated by middleware too).
        return $user->isAdminSatker() && $user->satker_id !== null;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Pegawai $pegawai): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdminSatker() && $user->satker_id === $pegawai->satker_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Pegawai $pegawai): bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return $user->isAdminSatker() && $user->satker_id === $pegawai->satker_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Pegawai $pegawai): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Pegawai $pegawai): bool
    {
        return false;
    }
}
