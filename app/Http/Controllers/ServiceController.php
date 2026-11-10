<?php

namespace App\Http\Controllers;

use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function update(Request $request, Service $service): RedirectResponse
    {
        $business = $request->user()->business()->firstOrFail();
        abort_unless($service->business_id === $business->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:600'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:240'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $service->update($data);

        return back()->with('success', 'Hizmet güncellendi.');
    }

    public function store(Request $request): RedirectResponse
    {
        $business = $request->user()->business()->firstOrFail();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:600'],
            'duration_minutes' => ['required', 'integer', 'min:15', 'max:240'],
            'price' => ['nullable', 'numeric', 'min:0', 'max:99999999.99'],
        ]);

        $business->services()->create($data + ['is_active' => true]);

        return back()->with('success', 'Hizmet eklendi.');
    }

    public function destroy(Request $request, Service $service): RedirectResponse
    {
        $business = $request->user()->business()->firstOrFail();
        abort_unless($service->business_id === $business->id, 404);

        $hasUpcomingAppointments = $business->appointments()
            ->where('service_id', $service->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->where('starts_at', '>=', now()->utc())
            ->exists();

        if ($hasUpcomingAppointments) {
            return back()->withErrors(['service' => 'Yaklaşan randevuları olan hizmet silinemez. Önce randevuları iptal et.']);
        }

        $service->update(['is_active' => false]);

        return back()->with('success', 'Hizmet arşivlendi; geçmiş randevu kayıtları korundu.');
    }
}
