<?php

namespace App\Models;

use App\Traits\BelongsToAgency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes, BelongsToAgency;

    protected $fillable = [
        'agency_id',
        'agent_id',
        'title',
        'slug',
        'description',
        'property_type',
        'category',
        'listing_type',
        'status',
        'price',
        'address',
        'city',
        'state',
        'zip_code',
        'bedrooms',
        'bathrooms',
        'area_sqft',
        'is_featured',
        'amenities',
    ];

    protected $casts = [
        'amenities' => 'array',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'area_sqft' => 'decimal:2',
    ];

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PropertyImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(PropertyImage::class)->where('is_primary', true);
    }
}
