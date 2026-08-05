<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlatformModule extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label',
        'description',
        'is_enabled',
        'is_addon',
        'addon_price_monthly',
        'addon_price_yearly',
        'included_in_plans',
        'sort_order',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'is_addon' => 'boolean',
        'included_in_plans' => 'array',
        'addon_price_monthly' => 'decimal:2',
        'addon_price_yearly' => 'decimal:2',
    ];
}
