<?php

namespace App\Http\Controllers\Api\Agency;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use App\Models\Role;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AgencyRoleController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();
        $roles = Role::where('agency_id', $user->agency_id)
            ->orWhere(fn($q) => $q->whereNull('agency_id')->where('is_system', true))
            ->with('permissions')
            ->get();

        $allPermissions = Permission::orderBy('module')->get()->groupBy('module');

        return response()->json([
            'roles' => $roles,
            'permissions' => $allPermissions,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Check plan limits
        $limitCheck = PlanLimitService::canCreateRole($user->agency_id);
        if (!$limitCheck['allowed']) {
            return response()->json([
                'message' => "Role limit reached for your plan. Maximum allowed: {$limitCheck['limit']}. Please upgrade your subscription.",
            ], 403);
        }

        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'display_name'   => 'nullable|string|max:255',
            'permissions'    => 'nullable|array',
            'permissions.*'  => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'agency_id'    => $user->agency_id,
            'name'         => \Illuminate\Support\Str::slug($validated['name'], '_'),
            'display_name' => $validated['display_name'] ?? $validated['name'],
            'is_system'    => false,
        ]);

        if (!empty($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return response()->json([
            'message' => 'Custom Role created successfully for your agency!',
            'role' => $role->load('permissions'),
        ], 201);
    }

    public function update(Request $request, Role $role): JsonResponse
    {
        $user = auth()->user();

        if ($role->agency_id !== $user->agency_id || $role->is_system) {
            return response()->json(['message' => 'You cannot modify system roles or roles from another agency.'], 403);
        }

        $validated = $request->validate([
            'display_name'  => 'required|string|max:255',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update(['display_name' => $validated['display_name']]);

        if (isset($validated['permissions'])) {
            $role->permissions()->sync($validated['permissions']);
        }

        return response()->json([
            'message' => 'Role permissions updated successfully.',
            'role' => $role->fresh('permissions'),
        ]);
    }

    public function destroy(Role $role): JsonResponse
    {
        $user = auth()->user();

        if ($role->agency_id !== $user->agency_id || $role->is_system) {
            return response()->json(['message' => 'Cannot delete system roles or roles from another agency.'], 403);
        }

        $role->delete();
        return response()->json(['message' => 'Role deleted successfully.']);
    }
}
