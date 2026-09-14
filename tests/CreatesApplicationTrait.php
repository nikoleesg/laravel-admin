<?php

trait CreatesApplicationTrait
{
    public function createApplication()
    {
        $app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';

        $app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Admin', \Encore\Admin\Facades\Admin::class);
        });

        $app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        // Start from a clean admin directory so `admin:install` in setUp()
        // regenerates the stubs, and the provider does not register a stale
        // routes.php at boot before the test config has been applied.
        $app['files']->deleteDirectory($app['config']->get('admin.directory', app_path('Admin')));

        $app->register(\Encore\Admin\AdminServiceProvider::class);

        return $app;
    }
}
