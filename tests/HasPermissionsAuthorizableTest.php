<?php

use Encore\Admin\Auth\Database\Permission;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Tests\Models\AppUser;

/**
 * HasPermissions must be usable on a model extending Foundation\Auth\User,
 * whose Authorizable trait already declares can()/cannot()/cant(). Any
 * signature narrower than Authorizable's is a PHP fatal at class load.
 */
class HasPermissionsAuthorizableTest extends TestCase
{
    public function testTraitOverridesAuthorizableOnFoundationUser()
    {
        $this->assertContains(Authorizable::class, class_uses_recursive(AppUser::class));

        $user = new AppUser;

        $this->assertTrue($user->can(''));
        $this->assertFalse($user->cannot(''));
        $this->assertFalse($user->cant(''));
    }

    public function testCannotAndCantCheckPermissionSlugs()
    {
        $permission = Permission::create(['slug' => 'create-post', 'name' => 'Create post']);

        $user = AppUser::create(['username' => 'editor', 'password' => bcrypt('secret'), 'name' => 'Editor']);
        $user->permissions()->attach($permission->id);
        $user = AppUser::find($user->id);

        $this->assertTrue($user->can('create-post'));
        $this->assertFalse($user->cannot('create-post'));
        $this->assertFalse($user->cant('create-post'));

        $this->assertFalse($user->can('delete-post'));
        $this->assertTrue($user->cannot('delete-post'));
        $this->assertTrue($user->cant('delete-post'));
    }

    public function testAdministratorRoleGrantsEverything()
    {
        $admin = AppUser::find(1);

        $this->assertTrue($admin->isAdministrator());
        $this->assertFalse($admin->cannot('anything-at-all'));
        $this->assertFalse($admin->cant('anything-at-all'));
    }
}
