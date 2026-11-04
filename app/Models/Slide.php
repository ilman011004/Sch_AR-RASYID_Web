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

    /**
     * Dipanggil ResourceController sebelum menyimpan.
     * Slide foto wajib punya foto, slide video wajib punya video.
     */
    public static function adminPrepare(array $data, User $user, ?self $existing): array
    {
        if (($data['type'] ?? 'foto') === 'video') {
            if (empty($data['video']) && ! $existing?->video) {
                throw ValidationException::withMessages(['video' => 'Slide video wajib mengunggah berkas video.']);
            }
        } elseif (empty($data['image']) && ! $existing?->image) {
            throw ValidationException::withMessages(['image' => 'Slide foto wajib mengunggah foto.']);
        }

        return $data;
    }
}