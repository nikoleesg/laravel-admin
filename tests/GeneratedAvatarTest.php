<?php

use Encore\Admin\Auth\Database\Administrator;
use Encore\Admin\Auth\GeneratedAvatar;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class GeneratedAvatarTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->be(Administrator::first(), 'admin');
    }

    public function testUserWithoutAvatarGetsGeneratedAvatarUrl()
    {
        $user = Administrator::first();

        $this->assertNull($user->getRawOriginal('avatar'));
        $this->assertSame(GeneratedAvatar::url($user->name), $user->avatar);
        $this->assertStringContainsString('/admin/_avatar_?name=', $user->avatar);
    }

    public function testAvatarRouteServesCachedPng()
    {
        $user = Administrator::first();

        Cache::forget(GeneratedAvatar::cacheKey($user->name));

        $response = $this->call('GET', $user->avatar);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('image/png', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('max-age=31536000', $response->headers->get('Cache-Control'));
        $this->assertStringStartsWith("\x89PNG", $response->getContent());

        $this->assertTrue(Cache::has(GeneratedAvatar::cacheKey($user->name)));
        $this->assertSame($response->getContent(), GeneratedAvatar::png($user->name));

        // Text-backed cache stores (e.g. the MySQL `cache` table) reject raw
        // binary, so the cached value must be plain ASCII.
        $cached = Cache::get(GeneratedAvatar::cacheKey($user->name));
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9+\/=]+$/', $cached);
        $this->assertSame($response->getContent(), base64_decode($cached));
    }

    public function testAvatarRouteRequiresAuthentication()
    {
        $url = Administrator::first()->avatar;

        auth('admin')->logout();

        $this->call('GET', $url)->assertRedirect();
    }

    public function testDisabledFallsBackToDefaultAvatar()
    {
        config([
            'admin.extensions.generated-avatar.enable' => false,
            'admin.default_avatar' => '/img/anonymous.png',
        ]);

        $this->assertSame(admin_asset('/img/anonymous.png'), Administrator::first()->avatar);
    }

    public function testUploadedAndExternalAvatarsAreUntouched()
    {
        $user = Administrator::first();

        $user->avatar = 'https://example.com/me.png';
        $this->assertSame('https://example.com/me.png', $user->avatar);

        $user->avatar = 'images/me.png';
        $this->assertSame(
            Storage::disk(config('admin.upload.disk'))->url('images/me.png'),
            $user->avatar
        );
    }

    public function testCacheKeyChangesWithThemeAndSize()
    {
        $default = GeneratedAvatar::cacheKey('admin');

        config(['admin.extensions.generated-avatar.size' => 64]);

        $this->assertNotSame($default, GeneratedAvatar::cacheKey('admin'));
    }
}
