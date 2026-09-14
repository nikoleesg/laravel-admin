<?php

namespace Encore\Admin\Controllers;

use Encore\Admin\Auth\GeneratedAvatar;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AvatarController extends Controller
{
    /**
     * Serve the generated avatar for the given name as a PNG.
     */
    public function show(Request $request)
    {
        $name = trim((string) $request->query('name', '')) ?: 'User';

        return response(GeneratedAvatar::png($name), 200, [
            'Content-Type' => 'image/png',
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
