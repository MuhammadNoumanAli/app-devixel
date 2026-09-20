<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Models\HR\HrSetting;
use App\Models\HR\Shift;
use Illuminate\Http\Request;

class HrSettingController extends Controller
{
    public function index()
    {
        if (!auth()->user()->can('hr-settings-manage') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $settings = HrSetting::instance();
        $shifts = Shift::orderBy('id')->get();

        return view('hr.settings.index', compact('settings', 'shifts'));
    }

    public function update(Request $request)
    {
        if (!auth()->user()->can('hr-settings-manage') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'grace_period_minutes' => 'required|integer|min:0|max:120',
            'late_deduction_percent' => 'required|numeric|min:0|max:100',
            'qualifying_lead_load_min_amount' => 'required|numeric|min:0',
            'qualifying_lead_max_days' => 'required|integer|min:1|max:365',
            'sales_agent_lead_bonus_amount' => 'required|numeric|min:0',
            'yearly_paid_leaves_quota' => 'required|integer|min:0|max:365',
            'max_paid_leaves_per_month' => 'required|integer|min:0|max:31',
            'weekend_days' => 'nullable|array',
            'days_in_month_mode' => 'required|in:30_days,actual_days,working_days',
        ]);

        $settings = HrSetting::instance();
        $settings->update($validated);

        return back()->with('success', 'HR settings updated successfully.');
    }

    public function storeShift(Request $request)
    {
        if (!auth()->user()->can('hr-shifts-manage') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'start_time' => 'required',
            'end_time' => 'required',
            'grace_minutes' => 'required|integer|min:0|max:120',
            'is_night_shift' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        Shift::create([
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'grace_minutes' => $validated['grace_minutes'],
            'is_night_shift' => $request->boolean('is_night_shift'),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Shift created successfully.');
    }

    public function updateShift(Request $request, $id)
    {
        if (!auth()->user()->can('hr-shifts-manage') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $shift = Shift::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'start_time' => 'required',
            'end_time' => 'required',
            'grace_minutes' => 'required|integer|min:0|max:120',
            'is_night_shift' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $shift->update([
            'name' => $validated['name'],
            'start_time' => $validated['start_time'],
            'end_time' => $validated['end_time'],
            'grace_minutes' => $validated['grace_minutes'],
            'is_night_shift' => $request->boolean('is_night_shift'),
            'is_active' => $request->boolean('is_active'),
        ]);

        return back()->with('success', 'Shift updated successfully.');
    }

    public function destroyShift($id)
    {
        if (!auth()->user()->can('hr-shifts-manage') && !auth()->user()->hasRole('Admin')) {
            abort(403, 'Unauthorized action.');
        }

        $shift = Shift::findOrFail($id);
        if ($shift->employees()->count() > 0) {
            return back()->with('error', 'Cannot delete shift with assigned employees.');
        }

        $shift->delete();

        return back()->with('success', 'Shift deleted successfully.');
    }
}
