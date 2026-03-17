<?php

use Laravel\BrowserKitTesting\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require_once __DIR__.'/CreatesApplicationTrait.php';

abstract class TestCase extends BaseTestCase
{
    use CreatesApplicationTrait;

    protected $baseUrl = 'https://localhost:8000';

    protected function setUp(): void
    {
        putenv('APP_ENV=testing');
        parent::setUp();
        
        $this->app['config']->set('app.env', 'testing');

        $adminConfig = require __DIR__.'/config/admin.php';

        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
        $this->app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $this->app['config']->set('filesystems', require __DIR__.'/config/filesystems.php');
        $this->app['config']->set('admin', $adminConfig);

        foreach (collect($adminConfig['auth'] ?? [])->dot()->toArray() as $key => $value) {
            $this->app['config']->set('auth.'.$key, $value);
        }

        $this->artisan('vendor:publish', [
            '--provider' => 'Encore\Admin\AdminServiceProvider',
            '--force' => true,
        ]);

        Schema::defaultStringLength(191);

        $this->artisan('admin:install');

        $this->migrateTestTables();

        if (file_exists($routes = admin_path('routes.php'))) {
            require $routes;
        }

        require __DIR__.'/routes.php';

        require __DIR__.'/seeds/factory.php';
    }

    protected function tearDown(): void
    {
        try {
            (new CreateAdminTables())->down();
            (new CreateTestTables())->down();
            DB::statement("DELETE FROM `migrations` WHERE `migration` = '2016_01_04_173148_create_admin_tables'");
        } catch (\Exception $e) {
            //
        }

        parent::tearDown();

        restore_error_handler();
        restore_exception_handler();
    }

    public function migrateTestTables()
    {
        require_once __DIR__.'/migrations/2016_11_22_093148_create_test_tables.php';
        (new CreateTestTables())->up();
    }

}
