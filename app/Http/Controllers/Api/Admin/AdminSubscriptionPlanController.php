<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlatformModule;
use App\Models\SubscriptionPlan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminSubscriptionPlanController extends Controller
{
    public function index(): JsonResponse
    {
        $plans = SubscriptionPlan::orderBy('sort_order')->get();
        $modules = PlatformModule::orderBy('sort_order')->get();

        return response()->json([
            'plans' => $plans,
            'modules' => $modules,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'description'    => 'nullable|string',
            'monthly_price'  => 'required|numeric|min:0',
            'yearly_price'   => 'required|numeric|min:0',
            'max_users'      => 'nullable|integer',
            'max_properties' => 'nullable|integer',
            'max_agents'     => 'nullable|integer',
            'max_roles'      => 'nullable|integer',
            'modules'        => 'nullable|array',
            'is_active'      => 'boolean',
        ]);

        $slug = Str::slug($validated['name']);

        $plan = SubscriptionPlan::create([
            ...$validated,
            'slug' => $slug,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => SubscriptionPlan::count() + 1,
        ]);

        return response()->json([
            'message' => 'Subscription Plan created successfully.',
            'plan' => $plan,
        ], 201);
    }

    public function update(Request $request, SubscriptionPlan $plan): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'description'    => 'nullable|string',
            'monthly_price'  => 'sometimes|numeric|min:0',
            'yearly_price'   => 'sometimes|numeric|min:0',
            'max_users'      => 'nullable|integer',
            'max_properties' => 'nullable|integer',
            'max_agents'     => 'nullable|integer',
            'max_roles'      => 'nullable|integer',
            'modules'        => 'nullable|array',
            'is_active'      => 'boolean',
        ]);

        $plan->update($validated);

        return response()->json([
            'message' => 'Subscription Plan updated successfully.',
            'plan' => $plan->fresh(),
        ]);
    }

    public function destroy(SubscriptionPlan $plan): JsonResponse
    {
        $plan->delete();
        return response()->json(['message' => 'Subscription Plan deleted successfully.']);
    }
}
