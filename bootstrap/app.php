<?php

use App\Http\Middleware\SetApiLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->throttleApi();
        $middleware->appendToGroup('api', SetApiLocale::class);

        // Admin pages: guests go to the admin login, logged-in admins skip it.
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isApi = fn (Request $request) => $request->is('api/*');

        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $isApi($request) || $request->expectsJson(),
        );

        // One plain answer for a missing route, a missing record and someone else's record,
        // so API clients never see model names or learn which ids exist.
        // (Laravel has already turned these into a 404 HTTP exception at this point.)
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($isApi) {
            if ($e->getStatusCode() === 404 && $isApi($request)) {
                return response()->json(['message' => 'Not found.'], 404);
            }
        });
    })->create();
