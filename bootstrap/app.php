<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
    $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // aksi ditolak (403) -> kembali ke daftar dengan penjelasan, bukan halaman kosong
        $exceptions->render(function (HttpExceptionInterface $e, Request $request) {
            if ($e->getStatusCode() !== 403 || $request->expectsJson() || ! $request->user()) {
                return null;
            }

            return redirect()->route('contents.index')->with(
                'err',
                'Aksi tidak dapat dilakukan. Kemungkinan status konten sudah berubah (misalnya revisi sudah terkirim atau konten sudah di-ACC), atau akun Anda tidak punya akses untuk aksi ini. Muat ulang daftar untuk melihat status terbaru.'
            );
        });
    })->create();
