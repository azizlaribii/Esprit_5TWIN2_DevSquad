<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class RepairRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'workshop_id',
        'photo_path',
        'ai_verdict',
        'defect_type',
        'location',
        'severity',
        'suggested_repairs',
        'bbox',
        'estimated_cost_min',
        'estimated_cost_max',
        'confidence',
        'status',
    ];

    protected $casts = [
        'suggested_repairs' => 'array',
        'bbox' => 'array',
        'estimated_cost_min' => 'float',
        'estimated_cost_max' => 'float',
        'confidence' => 'float',
    ];

    protected $appends = ['photo_url', 'severity_label'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function workshop(): BelongsTo
    {
        return $this->belongsTo(Workshop::class);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null;
    }

    public function getSeverityLabelAttribute(): string
    {
        return match ($this->severity) {
            'faible' => '🟡 Faible',
            'moyenne' => '🟠 Moyenne',
            'elevee' => '🔴 Élevée',
            default => '—',
        };
    }
}