<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Milestone extends Model
{
    protected $fillable = ['school_id', 'year', 'title', 'description', 'photo', 'sort'];

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function photoUrl(): ?string
    {
        return $this->photo ? Storage::disk('public')->url($this->photo) : null;
    }
}