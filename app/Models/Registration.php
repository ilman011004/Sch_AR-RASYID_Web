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

    protected function casts(): array
    {
        return ['birth_date' => 'date'];
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
        return config('school.ppdb_statuses.'.$this->status, $this->status);
    }
}