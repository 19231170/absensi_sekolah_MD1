<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class QrAuth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized. Please login first.'
                ], 401);
            }
            
            return redirect()->route('qr.login.form')
                           ->with('error', 'Silakan login terlebih dahulu.');
        }

        // Check if user has valid role
        $user = Auth::user();
        if (!in_array($user->role, ['admin', 'guru'])) {
            Auth::logout();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Invalid role.'
                ], 403);
            }
            
            return redirect()->route('qr.login.form')
                           ->with('error', 'Akses ditolak. Role tidak valid.');
        }

        // Check if user is still active
        if (!$user->is_active) {
            Auth::logout();
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Account deactivated.'
                ], 403);
            }
            
            return redirect()->route('qr.login.form')
                           ->with('error', 'Akun Anda telah dinonaktifkan.');
        }

        return $next($request);
    }
}
