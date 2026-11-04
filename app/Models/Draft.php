<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Draft extends Model
{
    protected $guarded = [];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(DraftComment::class)->orderBy('id');
    }

    public function displayName(): string
    {
        return match ($this->kind) {
            'link'  => $this->text,
            'text'  => 'Draft Teks',
            default => $this->name,
        };
    }
}
