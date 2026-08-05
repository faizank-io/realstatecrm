<?php

namespace App\Traits;

use App\Models\Agency;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToAgency
{
    protected static function bootBelongsToAgency(): void
    {
        // Global tenant scope for single database multi-tenancy
        static::addGlobalScope('agency_id', function (Builder $builder) {
            if (auth()->check()) {
                $user = auth()->user();
                
                // Super admin bypasses agency tenant scope
                if ($user->isSuperAdmin()) {
                    return;
                }

                if ($user->agency_id) {
                    $builder->where($builder->getQuery()->from . '.agency_id', $user->agency_id);
                }
            }
        });

        // Automatically assign current logged-in user's agency_id on creation
        static::creating(function (Model $model) {
            if (auth()->check() && ! $model->agency_id) {
                $user = auth()->user();
                if ($user->agency_id) {
                    $model->agency_id = $user->agency_id;
                }
            }
        });
    }

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }
}
