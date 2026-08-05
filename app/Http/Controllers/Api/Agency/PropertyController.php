<?php

namespace App\Http\Controllers\Api\Agency;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\ModuleAccessService;
use App\Services\PlanLimitService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PropertyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Module access check
        $moduleAccess = ModuleAccessService::canAccess('properties', $user->agency_id);
        if (!$moduleAccess['allowed']) {
            return response()->json(['message' => $moduleAccess['message']], 403);
        }

        $query = Property::with(['agent', 'primaryImage', 'images']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(fn($q) => $q->where('title', 'like', "%{$search}%")->orWhere('city', 'like', "%{$search}%"));
        }

        if ($request->filled('property_type')) {
            $query->where('property_type', $request->property_type);
        }

        if ($request->filled('listing_type')) {
            $query->where('listing_type', $request->listing_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(['properties' => $query->latest()->paginate(15)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();

        // Check module access
        $moduleAccess = ModuleAccessService::canAccess('properties', $user->agency_id);
        if (!$moduleAccess['allowed']) {
            return response()->json(['message' => $moduleAccess['message']], 403);
        }

        // Check plan limits
        $limitCheck = PlanLimitService::canAddProperty($user->agency_id);
        if (!$limitCheck['allowed']) {
            return response()->json([
                'message' => "Property limit reached for your plan. Maximum allowed: {$limitCheck['limit']}. Please upgrade your subscription plan.",
            ], 403);
        }

        $validated = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string',
            'property_type' => 'required|in:residential,commercial',
            'category'      => 'required|string',
            'listing_type'  => 'required|in:sale,rent',
            'status'        => 'required|in:draft,active,under_contract,sold,rented',
            'price'         => 'required|numeric|min:0',
            'address'       => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:100',
            'state'         => 'nullable|string|max:100',
            'zip_code'      => 'nullable|string|max:20',
            'bedrooms'      => 'nullable|integer|min:0',
            'bathrooms'     => 'nullable|integer|min:0',
            'area_sqft'     => 'nullable|numeric|min:0',
            'is_featured'   => 'boolean',
            'amenities'     => 'nullable|array',
            'agent_id'      => 'nullable|exists:users,id',
        ]);

        $slug = Str::slug($validated['title']) . '-' . Str::random(5);

        $property = Property::create([
            ...$validated,
            'agency_id' => $user->agency_id,
            'slug' => $slug,
            'agent_id' => $validated['agent_id'] ?? $user->id,
        ]);

        return response()->json([
            'message' => 'Property created successfully.',
            'property' => $property->load('agent'),
        ], 201);
    }

    public function show(Property $property): JsonResponse
    {
        return response()->json(['property' => $property->load(['agent', 'images'])]);
    }

    public function update(Request $request, Property $property): JsonResponse
    {
        $validated = $request->validate([
            'title'         => 'sometimes|string|max:255',
            'description'   => 'nullable|string',
            'property_type' => 'sometimes|in:residential,commercial',
            'category'      => 'sometimes|string',
            'listing_type'  => 'sometimes|in:sale,rent',
            'status'        => 'sometimes|in:draft,active,under_contract,sold,rented',
            'price'         => 'sometimes|numeric|min:0',
            'address'       => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:100',
            'bedrooms'      => 'nullable|integer|min:0',
            'bathrooms'     => 'nullable|integer|min:0',
            'area_sqft'     => 'nullable|numeric|min:0',
            'is_featured'   => 'boolean',
            'amenities'     => 'nullable|array',
            'agent_id'      => 'nullable|exists:users,id',
        ]);

        $property->update($validated);

        return response()->json([
            'message' => 'Property updated successfully.',
            'property' => $property->fresh(['agent', 'images']),
        ]);
    }

    public function destroy(Property $property): JsonResponse
    {
        $property->delete();
        return response()->json(['message' => 'Property deleted successfully.']);
    }
}
