<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssociationNeed extends Model
{
    use HasFactory;

    protected $fillable = [
        'association_id', 'category', 'age_group', 'size', 'gender', 'season',
        'quantity_needed', 'urgency', 'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at'      => 'date',
            'quantity_needed' => 'integer',
            'urgency'         => 'integer',
        ];
    }

    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }
}
