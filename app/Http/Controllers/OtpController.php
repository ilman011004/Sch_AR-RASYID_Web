<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use App\Support\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    // ---------- Register: verifikasi email (akun belum ada) ----------

    public function sendRegister(Request $request, School $school)
    {
        $data = $request->validate(['email' => 'required|email|max:150']);

        // Form daftar-akun memang sudah menolak email yang terdaftar (unique:users), jadi pesan ini tidak membuka informasi baru.
        if (User::where('email', Otp::normalize($data['email']))->exists()) {
            return response()->json(['message' => 'Email ini sudah terdaftar. Silakan masuk.'], 422);
        }

        if (Otp::send($data['email'], 'register', $school->name) === 'cooldown') {
            return response()->json(['message' => 'Tunggu 1 menit sebelum meminta kode baru.'], 429);
        }

        return response()->json(['message' => 'Kode dikirim ke email Anda. Cek juga folder spam.']);
    }

    public function verifyRegister(Request $request, School $school)
    {
        $data = $request->validate([
            'email' => 'required|email|max:150',
            'code' => 'required|digits:6',
        ]);

        if (! Otp::verify($data['email'], 'register', $data['code'])) {
            return response()->json(['message' => 'Kode salah atau kedaluwarsa.'], 422);
        }

        Otp::remember($data['email']);

        return response()->json(['message' => 'Email terverifikasi. Silakan lanjutkan pendaftaran akun.']);
    }

    // ---------- Login orang tua dengan kode (akun sudah ada) ----------

    public function sendLogin(Request $request, School $school)
    {
        $data = $request->validate(['email' => 'required|email|max:150']);

        if ($this->parentByEmail($data['email'])) {
            Otp::send($data['email'], 'login', $school->name);
        }

        // Respons selalu sama agar email yang terdaftar tidak bisa ditebak.
        return response()->json(['message' => 'Jika email terdaftar, kode telah dikirim. Cek juga folder spam.']);
    }

    public function verifyLogin(Request $request, School $school)
    {
        $data = $request->validate([
            'email' => 'required|email|max:150',
            'code' => 'required|digits:6',
        ]);

        $user = $this->parentByEmail($data['email']);

        if (! $user || ! Otp::verify($data['email'], 'login', $data['code'])) {
            return response()->json(['message' => 'Kode salah atau kedaluwarsa.'], 422);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return response()->json([
            'message' => 'Berhasil masuk.',
            'redirect' => $request->session()->pull('url.intended', route('ppdb.form', $school)),
        ]);
    }

    /** Hanya akun orang tua yang aktif. Akun admin panel tidak bisa masuk lewat sini. */
    private function parentByEmail(string $email): ?User
    {
        return User::where('email', Otp::normalize($email))
            ->where('role', 'parent')
            ->where('is_active', true)
            ->first();
    }
}