<?php

namespace App\Http\Controllers\Api\Agency;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Services\ModuleAccessService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();

        $moduleAccess = ModuleAccessService::canAccess('leads_crm', $user->agency_id);
        if (!$moduleAccess['allowed']) {
            return response()->json(['message' => $moduleAccess['message']], 403);
        }

        $query = Lead::with('assignedAgent');

        if ($request->filled('client_type')) {
            $query->where('client_type', $request->client_type);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(['leads' => $query->latest()->paginate(15)]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = auth()->user();

        $moduleAccess = ModuleAccessService::canAccess('leads_crm', $user->agency_id);
        if (!$moduleAccess['allowed']) {
            return response()->json(['message' => $moduleAccess['message']], 403);
        }

        $validated = $request->validate([
            'name'              => 'required|string|max:255',
            'email'             => 'nullable|email|max:255',
            'phone'             => 'nullable|string|max:50',
            'client_type'       => 'required|in:buyer,seller,landlord,tenant,investor',
            'status'            => 'required|in:new,contacted,qualified,lost,closed',
            'budget_min'        => 'nullable|numeric|min:0',
            'budget_max'        => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string',
            'assigned_agent_id' => 'nullable|exists:users,id',
        ]);

        $lead = Lead::create([
            ...$validated,
            'agency_id' => $user->agency_id,
            'assigned_agent_id' => $validated['assigned_agent_id'] ?? $user->id,
        ]);

        return response()->json([
            'message' => 'Lead created successfully.',
            'lead' => $lead->load('assignedAgent'),
        ], 201);
    }

    public function update(Request $request, Lead $lead): JsonResponse
    {
        $validated = $request->validate([
            'name'              => 'sometimes|string|max:255',
            'email'             => 'nullable|email|max:255',
            'phone'             => 'nullable|string|max:50',
            'client_type'       => 'sometimes|in:buyer,seller,landlord,tenant,investor',
            'status'            => 'sometimes|in:new,contacted,qualified,lost,closed',
            'budget_min'        => 'nullable|numeric|min:0',
            'budget_max'        => 'nullable|numeric|min:0',
            'notes'             => 'nullable|string',
            'assigned_agent_id' => 'nullable|exists:users,id',
        ]);

        $lead->update($validated);

        return response()->json([
            'message' => 'Lead updated successfully.',
            'lead' => $lead->fresh('assignedAgent'),
        ]);
    }

    public function destroy(Lead $lead): JsonResponse
    {
        $lead->delete();
        return response()->json(['message' => 'Lead deleted successfully.']);
    }
}
