<?php

namespace App\Services;

use App\Models\PlatformModule;

class ModuleAccessService
{
    /**
     * Check if a module is enabled globally by Super Admin.
     */
    public static function isGloballyEnabled(string $moduleKey): bool
    {
        $module = PlatformModule::where('key', $moduleKey)->first();
        if (!$module) return false;
        return (bool) $module->is_enabled;
    }

    /**
     * Check if a module is included in the agency's subscription plan.
     */
    public static function isInAgencyPlan(string $moduleKey, int $agencyId): bool
    {
        $module = PlatformModule::where('key', $moduleKey)->first();
        if (!$module) return false;

        $plan = PlanLimitService::getActivePlan($agencyId);
        if (!$plan) {
            // No active paid subscription = trialing/free mode (allow default modules)
            return true;
        }

        $planModules = $plan->modules ?? [];
        if (is_array($planModules) && in_array($moduleKey, $planModules)) {
            return true;
        }

        return false;
    }

    /**
     * Full module access check.
     */
    public static function canAccess(string $moduleKey, int $agencyId): array
    {
        if (!self::isGloballyEnabled($moduleKey)) {
            return [
                'allowed' => false,
                'message' => "The '{$moduleKey}' module is currently disabled by the platform administrator.",
            ];
        }

        if (!self::isInAgencyPlan($moduleKey, $agencyId)) {
            return [
                'allowed' => false,
                'message' => "Your current subscription plan does not include the '{$moduleKey}' module. Please upgrade your plan.",
            ];
        }

        return [
            'allowed' => true,
            'message' => 'Access granted.',
        ];
    }
}
