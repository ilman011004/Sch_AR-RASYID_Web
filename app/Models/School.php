<?php

namespace App\Models;

use App\Models\Advantage;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\Gallery;
use App\Models\Post;
use App\Models\Program;
use App\Models\Registration;
use App\Models\Slide;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class School extends Model
{
    protected $guarded = ['id'];

    private static ?self $foundation = null;

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'ppdb_open' => 'boolean',
            'ppdb_start' => 'date',
            'ppdb_end' => 'date',
        ];
    }

    /** URL memakai slug: /sd atau sd.domain */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(Gallery::class);
    }

    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }

    public function advantages(): HasMany
    {
        return $this->hasMany(Advantage::class);
    }

    public function faqs(): HasMany
    {
        return $this->hasMany(Faq::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ContactMessage::class);
    }

    public function levelLabel(): string
    {
        return config('school.levels.'.$this->level, strtoupper($this->level));
    }

    /** Baris khusus yayasan (level = yayasan). */
    public static function foundation(): self
    {
        return self::$foundation ??= static::where('level', 'yayasan')->first()
            ?? abort(503, 'Data yayasan belum ada. Jalankan: php artisan db:seed --class=FoundationSeeder');
    }

    /** Hanya jenjang pendidikan (tanpa baris yayasan). */
    public function scopeJenjang($q)
    {
        return $q->where('level', '!=', 'yayasan');
    }

    public function isFoundation(): bool
    {
        return $this->level === 'yayasan';
    }

    /** Teks "satu per baris" menjadi array. */
    public static function lines(?string $text): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', (string) $text))));
    }

    /** Isi ppdb_info menjadi bagian-bagian: baris "## Judul" = judul, baris lain = poin. */
    public function ppdbSections(): array
    {
        $sections = [];
        $current = null;

        foreach (self::lines($this->ppdb_info) as $line) {
            if (str_starts_with($line, '## ')) {
                $sections[] = ['title' => trim(substr($line, 3)), 'items' => []];
                $current = array_key_last($sections);
            } else {
                if ($current === null) {
                    $sections[] = ['title' => null, 'items' => []];
                    $current = array_key_last($sections);
                }
                $sections[$current]['items'][] = $line;
            }
        }

        return $sections;
    }

    public function missionList(): array
    {
        return self::lines($this->mission);
    }

    public function goalsList(): array
    {
        return self::lines($this->goals);
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? asset('storage/'.$this->logo) : null;
    }

    public function headPhotoUrl(): ?string
    {
        return $this->head_photo ? asset('storage/'.$this->head_photo) : null;
    }

    public function structureImageUrl(): ?string
    {
        return $this->structure_image ? asset('storage/'.$this->structure_image) : null;
    }

    public function safeColor(): string
    {
        return preg_match('/^#[0-9a-fA-F]{6}$/', (string) $this->primary_color) ? $this->primary_color : '#0f766e';
    }

    public function ppdbYear(): int
    {
        return (int) ($this->ppdb_year ?: now()->year);
    }

    /** Pendaftaran terbuka bila saklar aktif dan hari ini berada dalam rentang tanggal (jika diisi). */
    public function ppdbIsOpen(): bool
    {
        if (! $this->ppdb_open) {
            return false;
        }

        $today = today();

        if ($this->ppdb_start && $today->lt($this->ppdb_start)) {
            return false;
        }

        if ($this->ppdb_end && $today->gt($this->ppdb_end)) {
            return false;
        }

        return true;
    }
}