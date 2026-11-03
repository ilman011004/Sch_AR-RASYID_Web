<?php

namespace App\Models;

use App\Models\School;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['published_at' => 'datetime'];
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished(Builder $q): Builder
    {
        return $q->where('status', 'published')->where('published_at', '<=', now());
    }

    /** Tulisan tayang dari yayasan dan jenjang yang aktif (untuk situs yayasan). */
    public function scopeForPortal(Builder $q): Builder
    {
        return $q->published()->with('school')
            ->whereHas('school', fn ($s) => $s->where(
                fn ($w) => $w->where('level', 'yayasan')->orWhere('is_active', true)
            ));
    }

    /** Alamat publik tulisan. Relasi `school` harus sudah dimuat. */
    public function publicUrl(): string
    {
        return $this->school->isFoundation()
            ? route('portal.news.show', $this->slug)
            : route('news.show', ['school' => $this->school, 'slug' => $this->slug]);
    }

    public function typeLabel(): string
    {
        return $this->type === 'pengumuman' ? 'Pengumuman' : 'Berita';
    }

    /**
     * Dipanggil ResourceController sebelum menyimpan.
     * Mengatur slug, penulis, dan aturan persetujuan untuk operator.
     */
    public static function adminPrepare(array $data, User $user, ?self $existing): array
    {
        if (! $existing) {
            $base = Str::slug($data['title']) ?: 'berita';
            $slug = $base;
            $i = 2;
            while (static::where('school_id', $data['school_id'])->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i++;
            }
            $data['slug'] = $slug;
            $data['user_id'] = $user->id;
        }

        // Operator tidak boleh menerbitkan langsung; harus disetujui admin sekolah.
        if ($user->role === 'operator' && $data['status'] === 'published') {
            $data['status'] = 'pending';
        }

        if ($data['status'] === 'published' && empty($data['published_at'])) {
            $data['published_at'] = $existing?->published_at ?? now();
        }

        return $data;
    }
}