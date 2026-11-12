<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SliderPhoto extends Model
{
    protected $fillable = ['school_id', 'image', 'caption', 'video_url', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function url(): ?string
    {
        return $this->image ? asset('storage/' . $this->image) : null;
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

    /** Gambar pratinjau untuk halaman admin: foto sendiri, atau thumbnail YouTube. */
    public function previewUrl(): ?string
    {
        if ($this->image) {
            return $this->url();
        }

        return $this->youtubeId()
            ? 'https://img.youtube.com/vi/' . $this->youtubeId() . '/hqdefault.jpg'
            : null;
    }
}