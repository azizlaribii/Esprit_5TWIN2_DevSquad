<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DonationPhoto extends Model
{
    protected $fillable = ['donation_id', 'path'];

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }

    public function url(): string
    {
        return Storage::disk('public')->url($this->path);
    }
}
