<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAgencyController extends Controller
{
    public function index(): JsonResponse
    {
        $agencies = Agency::with(['owner', 'activeSubscription.plan'])
            ->withCount(['users', 'properties' => fn($q) => $q->withoutGlobalScopes()])
            ->latest()
            ->get();

        return response()->json(['agencies' => $agencies]);
    }

    public function updateStatus(Request $request, Agency $agency): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:trialing,active,suspended',
        ]);

        $agency->update(['status' => $validated['status']]);

        return response()->json([
            'message' => "Agency status updated to {$validated['status']}.",
            'agency' => $agency->fresh(),
        ]);
    }

    public function extendTrial(Request $request, Agency $agency): JsonResponse
    {
        $validated = $request->validate([
            'days' => 'required|integer|min:1|max:365',
        ]);

        $newTrialEnd = max(now(), $agency->trial_ends_at ?? now())->addDays($validated['days']);

        $agency->update([
            'status' => 'trialing',
            'trial_ends_at' => $newTrialEnd,
        ]);

        return response()->json([
            'message' => "Agency trial extended by {$validated['days']} days until {$newTrialEnd->toFormattedDateString()}.",
            'agency' => $agency->fresh(),
        ]);
    }

    public function assignPlan(Request $request, Agency $agency): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:subscription_plans,id',
            'billing_cycle' => 'required|in:monthly,yearly',
        ]);

        $plan = SubscriptionPlan::findOrFail($validated['plan_id']);

        // Deactivate previous subscriptions
        Subscription::where('agency_id', $agency->id)->update(['is_active' => false]);

        $price = $validated['billing_cycle'] === 'yearly' ? $plan->yearly_price : $plan->monthly_price;
        $durationMonths = $validated['billing_cycle'] === 'yearly' ? 12 : 1;

        $sub = Subscription::create([
            'agency_id' => $agency->id,
            'plan_id'   => $plan->id,
            'billing_cycle' => $validated['billing_cycle'],
            'amount'    => $price,
            'starts_at' => now(),
            'ends_at'   => now()->addMonths($durationMonths),
            'is_active' => true,
        ]);

        $agency->update(['status' => 'active']);

        return response()->json([
            'message' => "Assigned {$plan->name} to agency successfully.",
            'subscription' => $sub->load('plan'),
        ]);
    }
}
