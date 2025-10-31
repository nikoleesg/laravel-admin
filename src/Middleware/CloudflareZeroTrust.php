<?php


namespace Encore\Admin\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ViewErrorBag;

class CloudflareZeroTrust
{
    public function handle(Request $request, Closure $next)
    {
        // --- Ensure session started (needed for Auth::guard('admin')) ---
        if (!$request->hasSession() || !$request->session()->isStarted()) {
            $request->setLaravelSession(app('session')->driver());
            $request->session()->start();
        }

        if (!isset($errors)) {
            view()->share('errors', session()->get('errors', new ViewErrorBag));
        }

        // --- Cloudflare headers are trustworthy because the previous
        // middleware has already verified the JWT signature ---
        $email    = $request->header('cf-access-authenticated-user-email');
        $userName = $request->header('cf-access-authenticated-user-name')
            ?? strtok($email, '@'); // fallback: use email prefix as username

        if ($email) {
            // --- Use Laravel-Admin guard ---
            $guard = Auth::guard('admin');

            // --- Find or create the user ---
            $userModel = config('admin.database.users_model');

            $adminUser = $userModel::firstOrCreate(
                ['username' => $email],
                [
                    'name'     => $userName,
                    'email'    => $email,
                    'password' => bcrypt(str()->random(32)), // random unusable password
                ]
            );

            // --- Log in user if not already authenticated ---
            if (!$guard->check() || $guard->user()->id !== $adminUser->id) {
                $guard->login($adminUser);
            }
        }

        return $next($request);
    }
}