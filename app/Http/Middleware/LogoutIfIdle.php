<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class LogoutIfIdle
{
    /**
     * Logout otomatis jika pengguna tidak melakukan aktivitas (request)
     * lebih lama dari batas waktu di config('session.idle_timeout').
     * Contoh pemakaian di route: ->middleware('idle')
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $timeoutMinutes = config('session.idle_timeout');
        $lastActivity = $request->session()->get('last_activity');

        if (Auth::check() && $lastActivity && now()->timestamp - $lastActivity > $timeoutMinutes * 60) {
            Auth::logout();

            $request->session()->invalidate();
            $request->session()->regenerateToken();

            $message = "Sesi berakhir karena tidak ada aktivitas selama {$timeoutMinutes} menit. Silakan login kembali.";

            // Request dari JavaScript (fetch) dibalas JSON 401, bukan redirect
            if ($request->expectsJson()) {
                return response()->json(['message' => $message], 401);
            }

            return redirect()->route('login')->with('error', $message);
        }

        // Request otomatis di latar belakang (misalnya peta yang diperbarui tiap 30 detik)
        // bukan aktivitas pengguna, jadi tidak memperpanjang sesi.
        if (! $request->hasHeader('X-Background-Request')) {
            $request->session()->put('last_activity', now()->timestamp);
        }

        return $next($request);
    }
}
