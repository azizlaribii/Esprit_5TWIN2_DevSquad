<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class DonationPhoto extends Model
{
    use HasFactory;

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
