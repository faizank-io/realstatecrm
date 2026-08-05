<?php

namespace App\Models;

use App\Traits\BelongsToAgency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Document extends Model
{
    use HasFactory, BelongsToAgency;

    protected $fillable = [
        'agency_id',
        'title',
        'file_path',
        'file_type',
        'file_size',
        'documentable_type',
        'documentable_id',
    ];

    public function documentable(): MorphTo
    {
        return $this->morphTo();
    }
}
