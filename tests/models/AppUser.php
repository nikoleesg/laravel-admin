<?php

namespace Tests\Models;

use Encore\Admin\Auth\Database\HasPermissions;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User;

/**
 * Mimics an application User model (extends Foundation\Auth\User, which
 * pulls in Authorizable) that adds HasPermissions and is pointed at by
 * admin.database.users_model. Shares the admin users tables.
 */
class AppUser extends User
{
    use HasPermissions;

    protected $guarded = [];

    public function __construct(array $attributes = [])
    {
        $this->setTable(config('admin.database.users_table'));

        parent::__construct($attributes);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(config('admin.database.roles_model'), config('admin.database.role_users_table'), 'user_id', 'role_id');
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(config('admin.database.permissions_model'), config('admin.database.user_permissions_table'), 'user_id', 'permission_id');
    }
}
