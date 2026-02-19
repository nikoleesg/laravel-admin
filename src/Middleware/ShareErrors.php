<?php

namespace Encore\Admin\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;

class ShareErrors
{
    public function handle(Request $request, \Closure $next)
    {
        if ($request->ajax() || $request->pjax()) {
            return $next($request);
        }

        View::share('errors', session()->get('errors', new \Illuminate\Support\ViewErrorBag()));

        return $next($request);
    }
}
