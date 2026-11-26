<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias middleware buatan sendiri: ->middleware('role:admin'), ->middleware('idle')
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckUserRole::class,
            'idle' => \App\Http\Middleware\LogoutIfIdle::class,
        ]);

        // Pengguna yang sudah login lalu membuka /login diarahkan ke halaman sesuai role
        $middleware->redirectUsersTo(fn () => \App\Http\Controllers\AuthController::homeRoute());
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Token CSRF kedaluwarsa (HTTP 419), biasanya karena sesi sudah berakhir:
        // arahkan ke login dengan pesan, bukan menampilkan halaman error.
        $exceptions->render(function (HttpException $e, Request $request) {
            if ($e->getStatusCode() === 419 && ! $request->expectsJson()) {
                return redirect()->route('login')
                    ->with('error', 'Sesi Anda sudah berakhir. Silakan login kembali.');
            }
        });
    })->create();
