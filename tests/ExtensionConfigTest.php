<?php

use Encore\Admin\Admin;
use Encore\Admin\AdminServiceProvider;
use Encore\Admin\Grid\Filter;
use Encore\Admin\Grid\Filter\TimestampBetween;

class ExtensionConfigTest extends TestCase
{
    /**
     * The keys the package reads under `admin.extensions.*`, with the defaults
     * the code falls back to. The published config must ship every one of them.
     */
    protected $expectedDefaults = [
        'grid-lightbox' => ['enable' => true],
        'timestamp-between' => ['enable' => true],
        'daterangepicker' => ['config' => []],
        'data-table' => ['options' => []],
        'generated-avatar' => ['enable' => true, 'theme' => 'colorful', 'size' => 160],
    ];

    public function testPublishedConfigShipsNativeExtensionDefaults()
    {
        $config = require __DIR__.'/../config/admin.php';

        $this->assertArrayHasKey('extensions', $config);

        foreach ($this->expectedDefaults as $extension => $settings) {
            $this->assertArrayHasKey($extension, $config['extensions']);

            foreach ($settings as $key => $default) {
                $this->assertArrayHasKey($key, $config['extensions'][$extension]);
                $this->assertSame($default, $config['extensions'][$extension][$key]);
            }
        }
    }

    public function testNativeExtensionsAreRegisteredWithDefaultConfig()
    {
        $this->assertSame(TimestampBetween::class, $this->supportedFilters()['timestampBetween'] ?? null);

        $this->assertContains('vendor/laravel-admin/lightbox/magnific-popup.css', Admin::$css);
        $this->assertContains('vendor/laravel-admin/lightbox/jquery.magnific-popup.min.js', Admin::$js);
    }

    public function testTimestampBetweenFilterHonoursEnableToggle()
    {
        $this->forgetFilter('timestampBetween');

        config(['admin.extensions.timestamp-between.enable' => false]);
        $this->callProviderMethod('registerTimestampBetweenFilter');
        $this->assertArrayNotHasKey('timestampBetween', $this->supportedFilters());

        config(['admin.extensions.timestamp-between.enable' => true]);
        $this->callProviderMethod('registerTimestampBetweenFilter');
        $this->assertSame(TimestampBetween::class, $this->supportedFilters()['timestampBetween']);
    }

    public function testGridLightboxAssetsHonourEnableToggle()
    {
        Admin::$css = [];
        Admin::$js = [];

        config(['admin.extensions.grid-lightbox.enable' => false]);
        $this->callProviderMethod('registerGridLightboxExtension');
        $this->assertNotContains('vendor/laravel-admin/lightbox/magnific-popup.css', Admin::$css);
        $this->assertNotContains('vendor/laravel-admin/lightbox/jquery.magnific-popup.min.js', Admin::$js);

        config(['admin.extensions.grid-lightbox.enable' => true]);
        $this->callProviderMethod('registerGridLightboxExtension');
        $this->assertContains('vendor/laravel-admin/lightbox/magnific-popup.css', Admin::$css);
        $this->assertContains('vendor/laravel-admin/lightbox/jquery.magnific-popup.min.js', Admin::$js);
    }

    protected function callProviderMethod(string $method)
    {
        $provider = new AdminServiceProvider($this->app);

        $reflection = new ReflectionMethod($provider, $method);
        $reflection->setAccessible(true);

        return $reflection->invoke($provider);
    }

    protected function supportedFilters(): array
    {
        $property = new ReflectionProperty(Filter::class, 'supports');
        $property->setAccessible(true);

        return $property->getValue();
    }

    protected function forgetFilter(string $name)
    {
        $supports = $this->supportedFilters();
        unset($supports[$name]);

        $property = new ReflectionProperty(Filter::class, 'supports');
        $property->setAccessible(true);
        $property->setValue(null, $supports);
    }
}
