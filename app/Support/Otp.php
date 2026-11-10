<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class Otp
{
    private const TTL_MINUTES = 10;
    private const MAX_ATTEMPTS = 5;
    private const COOLDOWN_SECONDS = 60;
    private const SESSION_KEY = 'ppdb_email_verified';

    public static function normalize(string $email): string
    {
        return mb_strtolower(trim($email));
    }

    /**
     * Kirim kode baru.
     * @return string 'sent' atau 'cooldown'. Gagal kirim email melempar ValidationException.
     */
    public static function send(string $email, string $purpose, string $schoolName): string
    {
        $email = self::normalize($email);

        $recent = DB::table('otps')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->where('created_at', '>', now()->subSeconds(self::COOLDOWN_SECONDS))
            ->exists();

        if ($recent) {
            return 'cooldown';
        }

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // Kode lama yang belum dipakai dibatalkan.
        DB::table('otps')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->delete();

        $id = DB::table('otps')->insertGetId([
            'email' => $email,
            'purpose' => $purpose,
            'code_hash' => Hash::make($code),
            'expires_at' => now()->addMinutes(self::TTL_MINUTES),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $label = $purpose === 'login' ? 'Masuk' : 'Pendaftaran';

        try {
            Mail::raw(
                "Kode verifikasi {$label} {$schoolName}: {$code}\n\n"
                .'Berlaku '.self::TTL_MINUTES." menit. Jangan bagikan kode ini kepada siapa pun.\n"
                .'Abaikan email ini jika Anda tidak memintanya.',
                fn ($m) => $m->to($email)->subject("Kode Verifikasi {$label} — {$schoolName}")
            );
        } catch (\Throwable $e) {
            DB::table('otps')->where('id', $id)->delete();
            report($e);

            throw ValidationException::withMessages(['email' => 'Gagal mengirim email. Coba lagi nanti.']);
        }

        return 'sent';
    }

    /** Kode benar = kode langsung dihabiskan (sekali pakai). */
    public static function verify(string $email, string $purpose, string $code): bool
    {
        $email = self::normalize($email);

        $row = DB::table('otps')
            ->where('email', $email)
            ->where('purpose', $purpose)
            ->whereNull('consumed_at')
            ->where('expires_at', '>', now())
            ->latest('id')
            ->first();

        if (! $row || $row->attempts >= self::MAX_ATTEMPTS) {
            return false;
        }

        DB::table('otps')->where('id', $row->id)->increment('attempts');

        if (! Hash::check(trim($code), $row->code_hash)) {
            return false;
        }

        DB::table('otps')->where('id', $row->id)->update(['consumed_at' => now()]);

        return true;
    }

    // --- Khusus register: ingat email yang sudah terverifikasi di session ---

    public static function remember(string $email): void
    {
        session([self::SESSION_KEY => [
            'email' => self::normalize($email),
            'until' => now()->addMinutes(30)->timestamp,
        ]]);
    }

    public static function isVerified(string $email): bool
    {
        $v = session(self::SESSION_KEY);

        return $v
            && $v['email'] === self::normalize($email)
            && $v['until'] >= now()->timestamp;
    }

    public static function forget(): void
    {
        session()->forget(self::SESSION_KEY);
    }
}