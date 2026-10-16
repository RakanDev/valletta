<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        $supported = config('valletta.locales');
        $locale = $request->session()->get('locale', config('valletta.default_locale'));
        app()->setLocale(in_array($locale, $supported, true) ? $locale : 'ar');

        return $next($request);
    }
}
