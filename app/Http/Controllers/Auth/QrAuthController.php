<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class QrAuthController extends Controller
{
    /**
     * Tampilkan halaman login QR
     */
    public function showLoginForm()
    {
        return view('auth.qr-login');
    }

    /**
     * Step 1: Scan QR Code untuk identifikasi user
     */
    public function scanQr(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'qr_code' => 'required|string'
            ]);

            $qrCode = $request->qr_code;

            // Cari user berdasarkan QR code
            $user = User::where('qr_code', $qrCode)
                       ->active()
                       ->first();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'QR Code tidak ditemukan atau akun tidak aktif!'
                ], 404);
            }

            // Simpan user ID di session untuk step 2
            session(['qr_user_id' => $user->id]);

            return response()->json([
                'success' => true,
                'message' => "Selamat datang, {$user->name}!",
                'data' => [
                    'name' => $user->name,
                    'role' => $user->role_display,
                    'nip' => $user->nip,
                    'mata_pelajaran' => $user->mata_pelajaran,
                    'initials' => $user->initials
                ],
                'next_step' => 'pin'
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak valid: ' . implode(', ', $e->validator->errors()->all())
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in QR scan: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Step 2: Verifikasi PIN dan login
     */
    public function verifyPin(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'pin' => 'required|string|size:4'
            ]);

            $pin = $request->pin;
            $userId = session('qr_user_id');

            if (!$userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sesi QR tidak valid. Silakan scan QR Code terlebih dahulu.'
                ], 400);
            }

            $user = User::find($userId);

            if (!$user || !$user->is_active) {
                session()->forget('qr_user_id');
                return response()->json([
                    'success' => false,
                    'message' => 'User tidak ditemukan atau tidak aktif!'
                ], 404);
            }

            // Verifikasi PIN
            if (!$user->verifyPin($pin)) {
                return response()->json([
                    'success' => false,
                    'message' => 'PIN salah! Silakan coba lagi.'
                ], 400);
            }

            // Login user
            Auth::login($user);
            $user->updateLastLogin();
            
            // Clear session
            session()->forget('qr_user_id');

            return response()->json([
                'success' => true,
                'message' => 'Login berhasil!',
                'data' => [
                    'name' => $user->name,
                    'role' => $user->role,
                    'redirect_url' => $this->getRedirectUrl($user)
                ]
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'PIN harus 4 digit angka.'
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in PIN verification: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem. Silakan coba lagi.'
            ], 500);
        }
    }

    /**
     * Get redirect URL based on user role
     */
    private function getRedirectUrl(User $user): string
    {
        return match($user->role) {
            'admin' => route('admin.dashboard'),
            'guru' => route('guru.dashboard'),
            default => route('jadwal-kelas.index')
        };
    }

    /**
     * Logout user
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Logout berhasil!'
            ]);
        }

        return redirect()->route('qr.login.form')
                        ->with('success', 'Anda telah logout.');
    }

    /**
     * Clear QR session (jika user batalkan)
     */
    public function clearSession(Request $request): JsonResponse
    {
        session()->forget('qr_user_id');
        
        return response()->json([
            'success' => true,
            'message' => 'Sesi dibersihkan.'
        ]);
    }
}
