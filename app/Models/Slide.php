<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Validation\ValidationException;

class Slide extends Model
{
    protected $guarded = ['id'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function youtubeId(): ?string
    {
        if (! $this->video_url) {
            return null;
        }

        preg_match(
            '~(?:youtu\.be/|youtube\.com/(?:watch\?(?:.*&)?v=|embed/|shorts/|live/))([A-Za-z0-9_-]{11})~',
            $this->video_url,
            $m
        );

        return $m[1] ?? null;
    }

    /**
     * Dipanggil ResourceController sebelum menyimpan.
     * Slide foto wajib punya foto. Slide video wajib punya link YouTube atau berkas video.
     */
    public static function adminPrepare(array $data, User $user, ?self $existing): array
    {
        if (($data['type'] ?? 'foto') === 'video') {
            $hasYoutube = ! empty($data['video_url']);

            // Link harus benar-benar mengandung ID video yang valid.
            if ($hasYoutube) {
                $probe = new self(['video_url' => $data['video_url']]);
                if (! $probe->youtubeId()) {
                    throw ValidationException::withMessages([
                        'video_url' => 'Link YouTube tidak valid. Gunakan link video, bukan link kanal atau playlist.',
                    ]);
                }
            }

            $hasFile = ! empty($data['video']) || $existing?->video;

            if (! $hasYoutube && ! $hasFile) {
                throw ValidationException::withMessages([
                    'video_url' => 'Isi link YouTube atau unggah berkas video.',
                ]);
            }
        } else {
            if (empty($data['image']) && ! $existing?->image) {
                throw ValidationException::withMessages(['image' => 'Slide foto wajib mengunggah foto.']);
            }

            // Jenis foto: pastikan tidak ada link YouTube yang tersisa.
            $data['video_url'] = null;
        }

        return $data;
    }
}