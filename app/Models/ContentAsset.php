<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentAsset extends Model
{
    protected $guarded = [];

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }
}
