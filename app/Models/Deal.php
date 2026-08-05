<?php

namespace App\Models;

use App\Traits\BelongsToAgency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deal extends Model
{
    use HasFactory, BelongsToAgency;

    protected $fillable = [
        'agency_id',
        'property_id',
        'lead_id',
        'agent_id',
        'deal_amount',
        'commission_amount',
        'status',
        'closed_at',
    ];

    protected $casts = [
        'deal_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    public function property(): BelongsTo
    {
        return $this->belongsTo(Property::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }
}
