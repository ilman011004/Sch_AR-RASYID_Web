<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use App\Support\Otp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PpdbAuthController extends Controller
{
    /** Kunci session untuk data pendaftar yang menunggu verifikasi OTP. */
    private const PENDING = 'ppdb_pending_account';

    public function showLogin(School $school)
    {
        if ($redirect = $this->redirectIfLoggedIn($school)) {
            return $redirect;
        }

        return view('site.ppdb-login', ['school' => $school]);
    }

    public function login(Request $request, School $school)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Hanya akun role "parent" yang boleh masuk lewat sini (admin tetap lewat /admin/login).
        if (! Auth::attempt($credentials + ['is_active' => true, 'role' => 'parent'], $request->boolean('remember'))) {
            return back()->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau kata sandi salah.']);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('ppdb.form', $school));
    }

    public function showRegister(School $school)
    {
        if ($redirect = $this->redirectIfLoggedIn($school)) {
            return $redirect;
        }

        return view('site.ppdb-register', ['school' => $school]);
    }

    /** Langkah 1: simpan data sementara di session dan kirim kode. Akun BELUM dibuat. */
    public function register(Request $request, School $school)
    {
        // Email disamakan ke huruf kecil dulu, supaya pengecekan "sudah terdaftar" tidak terlewat.
        $request->merge(['email' => Otp::normalize((string) $request->input('email'))]);

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], [
            'name' => 'nama lengkap',
            'email' => 'email',
            'password' => 'kata sandi',
        ]);

        // Kirim kode dulu. Kalau email gagal terkirim, error tampil di form dan tidak ada yang tersimpan.
        $result = Otp::send($data['email'], 'register', $school->name);

        $request->session()->put(self::PENDING, [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']), // jangan simpan kata sandi asli di session
            'expires' => now()->addMinutes(30)->timestamp,
        ]);

        return redirect()->route('ppdb.verify', $school)->with(
            'status',
            $result === 'cooldown'
                ? 'Kode sebelumnya masih berlaku. Cek email Anda (juga folder spam).'
                : 'Kode verifikasi sudah dikirim ke '.$data['email'].'. Cek juga folder spam.'
        );
    }

    public function showVerify(Request $request, School $school)
    {
        if ($redirect = $this->redirectIfLoggedIn($school)) {
            return $redirect;
        }

        $pending = $this->pending($request);

        if (! $pending) {
            return redirect()->route('ppdb.register', $school)
                ->with('error', 'Sesi pendaftaran sudah habis. Silakan isi data akun lagi.');
        }

        return view('site.ppdb-verify', ['school' => $school, 'email' => $pending['email']]);
    }

    /** Langkah 2: kode benar = akun resmi dibuat. Pengguna lalu masuk sendiri lewat halaman login. */
    public function verify(Request $request, School $school)
    {
        $request->validate(['code' => 'required|digits:6'], [], ['code' => 'kode']);

        $pending = $this->pending($request);

        if (! $pending) {
            return redirect()->route('ppdb.register', $school)
                ->with('error', 'Sesi pendaftaran sudah habis. Silakan isi data akun lagi.');
        }

        if (! Otp::verify($pending['email'], 'register', $request->input('code'))) {
            return back()->withErrors(['code' => 'Kode salah atau kedaluwarsa.']);
        }

        // Jaga-jaga: email keburu didaftarkan orang lain selama menunggu kode.
        if (User::where('email', $pending['email'])->exists()) {
            $request->session()->forget(self::PENDING);

            return redirect()->route('ppdb.login', $school)
                ->with('error', 'Email ini sudah terdaftar. Silakan masuk.');
        }

        User::create([
            'name' => $pending['name'],
            'email' => $pending['email'],
            'password' => $pending['password'], // sudah berupa hash
            'role' => 'parent',
            'is_active' => true,
            'school_id' => null,
        ]);

        $request->session()->forget(self::PENDING);

        // Tidak login otomatis: arahkan ke halaman masuk, email sudah terisi.
        return redirect()->route('ppdb.login', $school)
            ->withInput(['email' => $pending['email']])
            ->with('status', 'Akun berhasil dibuat. Silakan masuk dengan email dan kata sandi yang baru Anda buat.');
    }

    public function resend(Request $request, School $school)
    {
        $pending = $this->pending($request);

        if (! $pending) {
            return redirect()->route('ppdb.register', $school)
                ->with('error', 'Sesi pendaftaran sudah habis. Silakan isi data akun lagi.');
        }

        if (Otp::send($pending['email'], 'register', $school->name) === 'cooldown') {
            return back()->withErrors(['code' => 'Tunggu 1 menit sebelum meminta kode baru.']);
        }

        return back()->with('status', 'Kode baru sudah dikirim. Cek juga folder spam.');
    }

    public function logout(Request $request, School $school)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ppdb', $school);
    }

    /** Data pendaftar yang menunggu verifikasi; null bila tidak ada atau sudah kedaluwarsa. */
    private function pending(Request $request): ?array
    {
        $pending = $request->session()->get(self::PENDING);

        if (! $pending || ($pending['expires'] ?? 0) < now()->timestamp) {
            $request->session()->forget(self::PENDING);

            return null;
        }

        return $pending;
    }

    /** Orang tua yang sudah login langsung ke formulir; admin yang login diberi penjelasan. */
    private function redirectIfLoggedIn(School $school)
    {
        if (! Auth::check()) {
            return null;
        }

        if (Auth::user()->isParent()) {
            return redirect()->route('ppdb.form', $school);
        }

        return redirect()->route('ppdb', $school)
            ->with('error', 'Anda sedang masuk sebagai admin. Untuk mendaftar, keluar dulu dari panel admin lalu gunakan akun orang tua / wali.');
    }
}