<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\Agency;
use App\Models\Role;
use App\Models\SubscriptionPlan;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\ModuleAccessService;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /**
     * Public Agency Signup (Starts Free Trial)
     */
    public function registerAgency(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'agency_name' => 'required|string|max:255',
            'owner_name'  => 'required|string|max:255',
            'email'       => 'required|email|max:255|unique:users,email',
            'password'    => 'required|string|min:8',
            'phone'       => 'nullable|string|max:50',
            'plan_slug'   => 'nullable|string|exists:subscription_plans,slug',
        ]);

        $trialDays = (int) (SystemSetting::getValue('general', 'default_trial_days', null, '14'));

        // 1. Create Agency
        $agency = Agency::create([
            'name' => $validated['agency_name'],
            'slug' => Str::slug($validated['agency_name']) . '-' . Str::random(4),
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'status' => 'trialing',
            'trial_started_at' => now(),
            'trial_ends_at' => now()->addDays($trialDays),
        ]);

        // 2. Get Agency Owner Role
        $ownerRole = Role::where('name', 'agency_owner')->first();

        // 3. Create Agency Owner User
        $user = User::create([
            'agency_id' => $agency->id,
            'role_id'   => $ownerRole?->id,
            'name'      => $validated['owner_name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'] ?? null,
            'status'    => 'active',
        ]);

        // Update Agency owner reference
        $agency->update(['owner_id' => $user->id]);

        Auth::login($user);

        return response()->json([
            'message' => 'Agency registered successfully with Free Trial!',
            'user'    => $user->load('agency', 'role'),
            'agency'  => $agency,
        ], 201);
    }

    /**
     * Standard Agency Login
     */
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid email or password.'], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            return response()->json(['message' => 'Your account is deactivated. Please contact support.'], 403);
        }

        // Agency check
        if ($user->agency_id) {
            $agency = $user->agency;
            if ($agency && $agency->status === 'suspended') {
                Auth::logout();
                return response()->json(['message' => 'Your agency account has been suspended. Please contact platform admin.'], 403);
            }
        }

        return response()->json([
            'message' => 'Logged in successfully.',
            'user' => $user->load(['agency', 'role.permissions']),
            'is_super_admin' => $user->isSuperAdmin(),
        ]);
    }

    /**
     * Secret Super Admin Login Endpoint (/super-admin/login)
     */
    public function superAdminLogin(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid admin credentials.'], 401);
        }

        /** @var User $user */
        $user = Auth::user();

        if (!$user->isSuperAdmin()) {
            Auth::logout();
            return response()->json(['message' => 'Access denied. You are not a Super Admin.'], 403);
        }

        return response()->json([
            'message' => 'Super Admin authenticated successfully.',
            'user' => $user->load('role'),
        ]);
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::logout();
        return response()->json(['message' => 'Logged out successfully.']);
    }

    /**
     * Get Current Authenticated User & Context
     */
    public function me(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (!$user) {
            return response()->json(['user' => null], 200);
        }

        $agency = $user->agency;
        $activePlan = $user->agency_id ? PlanLimitService::getActivePlan($user->agency_id) : null;

        return response()->json([
            'user' => $user->load(['agency', 'role.permissions']),
            'is_super_admin' => $user->isSuperAdmin(),
            'agency' => $agency,
            'active_plan' => $activePlan,
            'is_trialing' => $agency?->isTrialing() ?? false,
            'trial_days_remaining' => $agency?->trial_ends_at ? max(0, now()->diffInDays($agency->trial_ends_at, false)) : 0,
        ]);
    }
}
