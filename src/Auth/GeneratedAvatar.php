<?php

namespace Encore\Admin\Auth;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Laravolt\Avatar\Avatar;

/**
 * Initials avatars for users without an uploaded one.
 *
 * Rendering is done once per name by the `admin.avatar` route and the PNG
 * is kept in the cache, so model attribute access only ever builds a URL.
 */
class GeneratedAvatar
{
    const ROUTE = 'admin.avatar';

    /**
     * Whether generated avatars are switched on and the route is available.
     */
    public static function enabled(): bool
    {
        return (bool) config('admin.extensions.generated-avatar.enable', true)
            && Route::has(static::ROUTE);
    }

    /**
     * URL of the generated avatar for the given name.
     */
    public static function url(string $name): string
    {
        return route(static::ROUTE, ['name' => $name]);
    }

    /**
     * PNG bytes for the given name, rendered on first use and cached forever.
     */
    public static function png(string $name): string
    {
        return Cache::rememberForever(static::cacheKey($name), function () use ($name) {
            return static::render($name);
        });
    }

    /**
     * Cache key for a name; changes with the theme/size so stale renders
     * are never served after a config change.
     */
    public static function cacheKey(string $name): string
    {
        return 'admin-avatar:'.md5(static::theme().'|'.static::size().'|'.$name);
    }

    protected static function render(string $name): string
    {
        $size = static::size();

        return (new Avatar(config('laravolt.avatar', [])))
            ->create($name)
            ->setTheme(static::theme())
            ->setDimension($size, $size)
            ->setFontSize((int) round($size * 0.45))
            ->getImageObject()
            ->toPng()
            ->toString();
    }

    protected static function theme(): string
    {
        return (string) config('admin.extensions.generated-avatar.theme', 'colorful');
    }

    protected static function size(): int
    {
        return (int) config('admin.extensions.generated-avatar.size', 160);
    }
}
