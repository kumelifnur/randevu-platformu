<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class AppointmentStatusController extends Controller
{
    private const TRANSITIONS = [
        'pending' => ['confirmed', 'cancelled'],
        'confirmed' => ['cancelled', 'completed'],
        'cancelled' => [],
        'completed' => [],
    ];

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $business = $request->user()->business()->firstOrFail();
        abort_unless($appointment->business_id === $business->id, 404);

        $data = $request->validate(['status' => ['required', 'in:confirmed,cancelled,completed']]);
        $allowed = self::TRANSITIONS[$appointment->status] ?? [];

        if (! in_array($data['status'], $allowed, true)) {
            return back()->withErrors(['appointment' => 'Bu randevu durumu için seçilen geçişe izin verilmiyor.']);
        }

        $appointment->update(['status' => $data['status']]);

        return back()->with('success', 'Randevu durumu güncellendi.');
    }
}
