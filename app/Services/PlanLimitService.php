<?php

namespace App\Services;

use App\Models\Property;
use App\Models\Role;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use App\Models\User;

class PlanLimitService
{
    /**
     * Resolve active subscription plan for agency.
     */
    public static function getActivePlan(int $agencyId): ?SubscriptionPlan
    {
        $sub = Subscription::where('agency_id', $agencyId)
            ->where('is_active', true)
            ->with('plan')
            ->latest()
            ->first();

        return $sub?->plan;
    }

    private static function check(?int $limit, int $current): array
    {
        // null or -1 = unlimited
        if ($limit === null || $limit === -1) {
            return ['allowed' => true, 'limit' => 'Unlimited', 'current' => $current];
        }

        return [
            'allowed' => $current < $limit,
            'limit' => $limit,
            'current' => $current,
        ];
    }

    public static function canAddUser(int $agencyId): array
    {
        $plan = self::getActivePlan($agencyId);
        $current = User::where('agency_id', $agencyId)->count();
        return self::check($plan?->max_users, $current);
    }

    public static function canAddProperty(int $agencyId): array
    {
        $plan = self::getActivePlan($agencyId);
        $current = Property::where('agency_id', $agencyId)->count();
        return self::check($plan?->max_properties, $current);
    }

    public static function canCreateRole(int $agencyId): array
    {
        $plan = self::getActivePlan($agencyId);
        $current = Role::where('agency_id', $agencyId)->where('is_system', false)->count();
        return self::check($plan?->max_roles, $current);
    }
}
