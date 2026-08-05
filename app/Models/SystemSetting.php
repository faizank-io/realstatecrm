<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'group',
        'key',
        'value',
        'agency_id',
    ];

    public static function getValue(string $group, string $key, ?int $agencyId = null, $default = null)
    {
        $setting = static::where('group', $group)
            ->where('key', $key)
            ->where('agency_id', $agencyId)
            ->first();

        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $group, string $key, $value, ?int $agencyId = null): self
    {
        return static::updateOrCreate(
            ['group' => $group, 'key' => $key, 'agency_id' => $agencyId],
            ['value' => $value]
        );
    }
}
