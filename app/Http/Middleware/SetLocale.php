<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->query('lang') ?? session('locale') ?? config('app.locale', 'az');

        if (!in_array($locale, ['az', 'ru', 'en'])) {
            $locale = config('app.locale', 'az');
        }

        if ($request->query('lang')) {
            session(['locale' => $locale]);
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
