<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // An upload bigger than the server's post_max_size arrives with no data at
        // all. Send the admin back with a plain message instead of an error page.
        // This check runs before the session starts, so the message travels in
        // the address (?too_large=1) and the form shows it.
        $exceptions->render(function (PostTooLargeException $e, Request $request) {
            if ($request->expectsJson()) {
                return null;
            }
            $back = $request->headers->get('referer') ?: url('/admin/resources/create');
            $back = preg_replace('/([?&])too_large=1(&|$)/', '$1', $back);

            return redirect()->to(rtrim($back, '?&').(str_contains($back, '?') ? '&' : '?').'too_large=1');
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
