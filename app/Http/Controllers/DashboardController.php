<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $business = $request->user()->business()->with(['services', 'hours'])->firstOrFail();
        $localToday = now($business->timezone);
        $todayStart = $localToday->copy()->startOfDay()->utc();
        $todayEnd = $localToday->copy()->endOfDay()->utc();
        $weekEnd = $localToday->copy()->addDays(7)->endOfDay()->utc();

        $appointments = $business->appointments()
            ->with('service')
            ->where('starts_at', '>=', now()->utc())
            ->whereIn('status', ['pending', 'confirmed'])
            ->orderBy('starts_at')
            ->paginate(12);

        $todayCount = $business->appointments()
            ->where('starts_at', '>=', $todayStart)
            ->where('starts_at', '<=', $todayEnd)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $weekCount = $business->appointments()
            ->where('starts_at', '>=', now()->utc())
            ->where('starts_at', '<=', $weekEnd)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        return view('dashboard.index', compact('business', 'appointments', 'todayCount', 'weekCount'));
    }
}
