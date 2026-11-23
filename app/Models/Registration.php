<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Registration extends Model
{
    protected $guarded = ['id'];

    /** Kolom dokumen yang boleh diunduh admin. */
    public const DOCS = [
        'doc_birth_cert' => 'Akta kelahiran',
        'doc_family_card' => 'Kartu keluarga',
        'doc_photo' => 'Pas foto',
    ];

    public const STAGES = [
        1 => 'Pengumpulan berkas',
        2 => 'Seleksi',
        3 => 'Wawancara',
        4 => 'Selesai',
    ];

    public const STATUSES = [
        'diproses' => 'Dalam proses',
        'diterima' => 'Diterima',
        'ditolak' => 'Tidak diterima',
    ];

    public const TEST_MODES = [
        'online' => 'Online (daring)',
        'offline' => 'Offline (datang ke sekolah)',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'interview_at' => 'datetime',
            'test_at' => 'datetime',
            'docs_verified' => 'boolean',
            'test_score' => 'decimal:2',
            'custom_fields' => 'array',
        ];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    /** Akun orang tua pemilik pendaftaran. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function stageLabel(): string
    {
        return self::STAGES[$this->stage] ?? '-';
    }

    public function testModeLabel(): ?string
    {
        return self::TEST_MODES[$this->test_mode] ?? null;
    }

    /** Semua dokumen wajib sudah diunggah. */
    public function docsComplete(): bool
    {
        foreach (array_keys(self::DOCS) as $field) {
            if (empty($this->{$field})) {
                return false;
            }
        }

        return true;
    }

    public function scoreLabel(): ?string
    {
        if ($this->test_score === null) {
            return null;
        }

        return rtrim(rtrim(number_format((float) $this->test_score, 2, ',', '.'), '0'), ',');
    }

    /** Baris-baris jadwal tes seleksi (kosong bila belum diisi). */
    public function testScheduleText(): string
    {
        if (! $this->test_at) {
            return '';
        }

        $isOnline = $this->test_mode === 'online';

        $lines = [
            'Waktu : '.$this->test_at->translatedFormat('l, d F Y').', pukul '.$this->test_at->format('H:i'),
            'Cara  : '.($this->testModeLabel() ?? '-'),
            ($isOnline ? 'Tautan/platform: ' : 'Tempat: ').$this->test_place,
        ];

        if ($this->test_info) {
            $lines[] = 'Catatan: '.$this->test_info;
        }

        return implode("\n", $lines);
    }

    /** Baris-baris jadwal wawancara (kosong bila belum diisi). */
    public function interviewScheduleText(): string
    {
        if (! $this->interview_at) {
            return '';
        }

        $lines = [
            'Waktu : '.$this->interview_at->translatedFormat('l, d F Y').', pukul '.$this->interview_at->format('H:i'),
            'Tempat: '.$this->interview_place,
        ];

        if ($this->interview_note) {
            $lines[] = 'Catatan: '.$this->interview_note;
        }

        return implode("\n", $lines);
    }

    /** Isi pesan sesuai tahap saat ini. Dipakai untuk email dan WhatsApp. */
    public function stageMessage(): string
    {
        $name = $this->student_name;
        $no = $this->reg_number;
        $school = $this->school->name;
        $note = $this->admin_note ? "\n\nCatatan sekolah: {$this->admin_note}" : '';

        if ($this->stage >= 4) {
            return $this->status === 'diterima'
                ? "Alhamdulillah, selamat! {$name} ({$no}) dinyatakan DITERIMA di {$school}. "
                    ."Silakan menghubungi sekolah untuk informasi daftar ulang.{$note}"
                : "Mohon maaf, {$name} ({$no}) belum dapat kami terima di {$school} pada penerimaan kali ini. "
                    ."Terima kasih telah mendaftar dan semoga sukses selalu.{$note}";
        }

        if ($this->stage === 3) {
            $head = "Selamat, {$name} ({$no}) lolos Tahap 2 (Seleksi) dan melanjutkan ke Tahap 3: Wawancara.";
            $schedule = $this->interviewScheduleText();

            return $schedule !== ''
                ? "{$head}\n\nJadwal wawancara:\n{$schedule}\n\nMohon hadir tepat waktu bersama orang tua/wali.{$note}"
                : "{$head}\n\nJadwal wawancara akan kami informasikan segera.{$note}";
        }

        if ($this->stage === 2) {
            $head = "Selamat, {$name} ({$no}) lolos Tahap 1 (Pengumpulan Berkas) dan melanjutkan ke Tahap 2: Seleksi.";
            $schedule = $this->testScheduleText();

            return $schedule !== ''
                ? "{$head}\n\nJadwal tes seleksi:\n{$schedule}\n\nMohon hadir/bergabung tepat waktu.{$note}"
                : "{$head}\n\nJadwal tes seleksi (waktu serta online atau offline) akan kami informasikan segera.{$note}";
        }

        return "Pendaftaran {$name} ({$no}) sudah kami terima di {$school}. "
            ."Saat ini berkas sedang diperiksa panitia. Kami akan menghubungi Anda bila berkas sudah dinyatakan lengkap.{$note}";
    }

    /** Tautan WhatsApp berisi pesan siap kirim ke orang tua. */
    public function waLink(): ?string
    {
        $phone = preg_replace('/\D+/', '', (string) $this->parent_phone);

        if ($phone === '') {
            return null;
        }

        if (str_starts_with($phone, '0')) {
            $phone = '62'.substr($phone, 1);
        }

        $text = "Assalamualaikum, Bapak/Ibu {$this->parent_name}.\n\n"
            .$this->stageMessage()
            ."\n\nPantau status pendaftaran: ".route('ppdb.status', $this->school)
            ."\n\n".$this->school->name;

        return 'https://wa.me/'.$phone.'?text='.rawurlencode($text);
    }
}