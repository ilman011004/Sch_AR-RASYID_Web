<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SliderPhoto extends Model
{
    protected $fillable = ['school_id', 'image', 'caption', 'sort_order', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function url(): string
    {
        return asset('storage/' . $this->image);
    }
}