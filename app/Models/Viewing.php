<?php

namespace App\Models;

use App\Traits\BelongsToAgency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Viewing extends Model
{
    use HasFactory, BelongsToAgency;

    protected $fillable = [
        'agency_id',
        'property_id',
        'agent_id',
        'lead_id',
        'scheduled_at',
        'status',
        'feedback_notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }
}
