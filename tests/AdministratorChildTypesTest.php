<?php

use Encore\Admin\Auth\Database\Administrator;
use Tests\Models\ManagerAdministrator;

class AdministratorChildTypesTest extends TestCase
{
    public function testAliasInTypeColumnResolvesThroughConfigMap()
    {
        config(['admin.database.user_types' => ['manager' => ManagerAdministrator::class]]);

        $manager = ManagerAdministrator::create(['username' => 'manager', 'password' => bcrypt('secret'), 'name' => 'Manager']);

        $this->assertSame('manager', $manager->getRawOriginal('type'));

        $this->assertInstanceOf(ManagerAdministrator::class, Administrator::find($manager->id));
        $this->assertInstanceOf(Administrator::class, Administrator::find(1));
        $this->assertNotInstanceOf(ManagerAdministrator::class, Administrator::find(1));
    }

    public function testTypeColumnFallsBackToClassNameWithoutMap()
    {
        config(['admin.database.user_types' => []]);

        $manager = ManagerAdministrator::create(['username' => 'manager', 'password' => bcrypt('secret'), 'name' => 'Manager']);

        $this->assertSame(ManagerAdministrator::class, $manager->getRawOriginal('type'));
        $this->assertInstanceOf(ManagerAdministrator::class, Administrator::find($manager->id));
    }
}
