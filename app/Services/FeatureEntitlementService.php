<?php

namespace App\Services;

use App\Models\User;
use App\Models\FeatureFlag;
use App\Models\UserFeatureOverride;
use App\Models\Subscription;

class FeatureEntitlementService
{
    /**
     * Determine if a user has access to a feature.
     *
     * Priority: Admin Override -> Subscription Plan Entitlement -> Default State (Legacy Fallback or Global Flag)
     */
    public static function check(User $user, string $featureKey): bool
    {
        // 1. Admin Override (Priority 1)
        $override = UserFeatureOverride::where('user_id', $user->id)
            ->where('feature_key', $featureKey)
            ->first();

        if ($override !== null) {
            return (bool) $override->enabled;
        }

        // 2. Subscription Plan Entitlement (Priority 2)
        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->where('expires_at', '>', now())
            ->first();

        if ($activeSubscription !== null) {
            $plan = $activeSubscription->plan;
            if ($plan !== null) {
                if ($featureKey === 'skills' && $plan->elite_skill_access) {
                    return true;
                }
                if ($featureKey === 'domains' && $plan->personal_domain_access) {
                    return true;
                }
                if ($featureKey === 'contracts') {
                    // Active paid plans allow access
                    return true;
                }
            }
        }

        // 3. Default State / Legacy Fallback (Priority 3)
        if ($featureKey === 'contracts' && $user->is_contracts_paid) {
            return true;
        }
        if ($featureKey === 'domains' && $user->is_domains_paid) {
            return true;
        }
        if ($featureKey === 'skills' && $user->is_skills_paid) {
            return true;
        }

        // Check global feature flag
        $flag = FeatureFlag::where('feature_key', $featureKey)->first();
        if ($flag !== null) {
            return (bool) $flag->enabled;
        }

        return false;
    }

    /**
     * Check if a feature is globally enabled.
     */
    public static function isGloballyEnabled(string $featureKey): bool
    {
        $flag = FeatureFlag::where('feature_key', $featureKey)->first();
        return $flag ? (bool) $flag->enabled : true;
    }
}
