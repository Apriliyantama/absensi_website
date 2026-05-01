<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class TeacherMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user(); // ini sekarang valid
        // dd($user->role, $user->status);

        if (!$user || $user->role !== 'teacher') {
            abort(403);
        }

        if ($user->status !== 'approved') {
            abort(403, 'Akun belum disetujui');
        }

        return $next($request);
    }
}