<?php

namespace App\Models\Concerns;

use App\Models\Role;

trait HasRoles
{
    /**
     * User Roles
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(Role::class);
    }

    /**
     * Check if the user has a Role
     *
     * @param string $role
     * @return boolean
     */
    public function hasRole($role)
    {
        return $this->roles()->where('name', $role)->count() === 1;
    }
}
