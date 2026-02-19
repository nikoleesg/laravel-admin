<?php

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplicationTrait;

    protected $baseUrl = 'http://localhost:8000';

    protected function setUp(): void
    {
        parent::setUp();

        $adminConfig = require __DIR__.'/config/admin.php';

        $this->app['config']->set('database.default', env('DB_CONNECTION', 'mysql'));
        $this->app['config']->set('database.connections.mysql.host', env('MYSQL_HOST', 'localhost'));
        $this->app['config']->set('database.connections.mysql.database', env('MYSQL_DATABASE', 'laravel_admin_test'));
        $this->app['config']->set('database.connections.mysql.username', env('MYSQL_USER', 'root'));
        $this->app['config']->set('database.connections.mysql.password', env('MYSQL_PASSWORD', ''));
        $this->app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $this->app['config']->set('filesystems', require __DIR__.'/config/filesystems.php');
        $this->app['config']->set('admin', $adminConfig);

        foreach (collect($adminConfig['auth'] ?? [])->dot()->toArray() as $key => $value) {
            $this->app['config']->set('auth.'.$key, $value);
        }

        $this->artisan('vendor:publish', ['--provider' => 'Encore\Admin\AdminServiceProvider']);

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
        (new CreateAdminTables())->down();
        (new CreateTestTables())->down();
        DB::statement("DELETE FROM `migrations` WHERE `migration` = '2016_01_04_173148_create_admin_tables'");

        parent::tearDown();
    }

    public function migrateTestTables()
    {
        require_once __DIR__.'/migrations/2016_11_22_093148_create_test_tables.php';
        (new CreateTestTables())->up();
    }

    protected function be($user, $guard = null)
    {
        $this->actingAs($user, $guard);
        return $this;
    }

    protected function see($text, $element = null)
    {
        if ($element) {
            $this->assertSelectorTextContains($element, $text);
        } else {
            $this->assertSee($text);
        }
        return $this;
    }

    protected function dontSee($text, $element = null)
    {
        if ($element) {
            $this->assertSelectorTextNotContains($element, $text);
        } else {
            $this->assertDontSee($text);
        }
        return $this;
    }

    protected function visit($uri)
    {
        return $this->get($uri);
    }

    protected function seePageIs($uri)
    {
        $this->assertLocation($uri);
        return $this;
    }

    protected function seeInDatabase($table, $data = [])
    {
        $this->assertDatabaseHas($table, $data);
        return $this;
    }

    protected function missingFromDatabase($table, $data = [])
    {
        $this->assertDatabaseMissing($table, $data);
        return $this;
    }

    protected function seeIsAuthenticated($guard = null)
    {
        $this->assertAuthenticated($guard);
        return $this;
    }

    protected function dontSeeIsAuthenticated($guard = null)
    {
        $this->assertGuest($guard);
        return $this;
    }

    protected function submitForm($buttonText, $formData = [])
    {
        $this->call('POST', $this->currentUri, $formData);
        return $this;
    }
}
