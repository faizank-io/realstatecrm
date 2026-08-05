<?php

namespace App\Http\Controllers\Api\Agency;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AgencyTeamController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $members = User::where('agency_id', $user->agency_id)
            ->with('role')
            ->latest()
            ->get();

        $roles = Role::where('agency_id', $user->agency_id)
            ->orWhere(fn($q) => $q->whereNull('agency_id')->where('is_system', true))
            ->get();

        return response()->json([
            'members' => $members,
            'roles' => $roles,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Plan user limit check
        $limitCheck = PlanLimitService::canAddUser($user->agency_id);
        if (!$limitCheck['allowed']) {
            return response()->json([
                'message' => "Team member limit reached for your subscription plan. Maximum allowed: {$limitCheck['limit']}. Please upgrade your plan.",
            ], 403);
        }

        $validated = $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'phone'    => 'nullable|string|max:50',
            'role_id'  => 'required|exists:roles,id',
        ]);

        $member = User::create([
            'agency_id' => $user->agency_id,
            'role_id'   => $validated['role_id'],
            'name'      => $validated['name'],
            'email'     => $validated['email'],
            'password'  => Hash::make($validated['password']),
            'phone'     => $validated['phone'] ?? null,
            'status'    => 'active',
        ]);

        return response()->json([
            'message' => 'Team member added to agency successfully.',
            'member'  => $member->load('role'),
        ], 201);
    }

    public function updateStatus(Request $request, User $user): JsonResponse
    {
        $currentUser = auth()->user();

        if ($user->agency_id !== $currentUser->agency_id) {
            return response()->json(['message' => 'Unauthorized.'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $user->update(['status' => $validated['status']]);

        return response()->json([
            'message' => "Team member status updated to {$validated['status']}.",
            'member' => $user->fresh('role'),
        ]);
    }
}
