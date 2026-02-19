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

        $app->register(\Encore\Admin\AdminServiceProvider::class);

        return $app;
    }
}
