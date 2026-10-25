<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.only' => \App\Http\Middleware\EnsureAdministrator::class,
            'account.active' => \App\Http\Middleware\EnsureAccountActive::class,
        ]);
        $middleware->web(append: [\App\Http\Middleware\SecurityHeaders::class]);
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

        // Links to stories on the old WordPress site (for example /2023/05/my-story/)
        // open the same story here once the news has been imported.
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (! $request->isMethod('GET') || $request->is('admin/*', 'storage/*')) {
                return null;
            }
            $path = rawurldecode(trim($request->path(), '/'));
            try {
                $post = \App\Models\Post::published()->where('legacy_path', $path)->first();
            } catch (\Throwable) {
                return null;   // database not ready
            }

            return $post ? redirect()->route('news.show', $post, 301) : null;
        });

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
