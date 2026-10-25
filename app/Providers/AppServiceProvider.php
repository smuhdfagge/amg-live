<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Public forms: enough for real people, too few for a spam script.
        RateLimiter::for('forms', fn (Request $request) => Limit::perMinute(8)->by('forms|'.$request->ip()));

        // Admin sign-in: limited per email address and per IP, kept separate from the public forms.
        RateLimiter::for('login', fn (Request $request) => [
            Limit::perMinute(5)->by('login|'.strtolower((string) $request->input('email')).'|'.$request->ip()),
            Limit::perMinute(20)->by('login-ip|'.$request->ip()),
        ]);
    }
}
