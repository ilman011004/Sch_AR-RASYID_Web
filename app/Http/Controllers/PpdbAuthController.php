<?php

namespace App\Http\Controllers;

use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class PpdbAuthController extends Controller
{
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

    public function register(Request $request, School $school)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email',
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [], [
            'name' => 'nama lengkap',
            'email' => 'email',
            'password' => 'kata sandi',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'], // di-hash otomatis oleh cast
            'role' => 'parent',
            'is_active' => true,
            'school_id' => null,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('ppdb.form', $school));
    }

    public function logout(Request $request, School $school)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('ppdb', $school);
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