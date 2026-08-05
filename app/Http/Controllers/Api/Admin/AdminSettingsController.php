<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function index(): JsonResponse
    {
        $trialDays = SystemSetting::getValue('general', 'default_trial_days', null, '14');
        $siteName  = SystemSetting::getValue('general', 'site_name', null, 'Real Estate SaaS');

        return response()->json([
            'default_trial_days' => (int) $trialDays,
            'site_name' => $siteName,
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'default_trial_days' => 'required|integer|min:1|max:365',
            'site_name' => 'required|string|max:255',
        ]);

        SystemSetting::setValue('general', 'default_trial_days', (string) $validated['default_trial_days']);
        SystemSetting::setValue('general', 'site_name', $validated['site_name']);

        return response()->json(['message' => 'System Settings updated successfully.']);
    }
}
