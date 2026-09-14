<?php

use Encore\Admin\AdminServiceProvider;
use Encore\Admin\Facades\Admin;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\AliasLoader;

trait CreatesApplicationTrait
{
    public function createApplication()
    {
        $app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';

        $app->booting(function () {
            $loader = AliasLoader::getInstance();
            $loader->alias('Admin', Admin::class);
        });

        $app->make(Kernel::class)->bootstrap();

        // Start from a clean admin directory so `admin:install` in setUp()
        // regenerates the stubs, and the provider does not register a stale
        // routes.php at boot before the test config has been applied.
        $app['files']->deleteDirectory($app['config']->get('admin.directory', app_path('Admin')));

        $app->register(AdminServiceProvider::class);

        return $app;
    }
}
