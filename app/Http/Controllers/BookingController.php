<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Business;
use App\Models\Service;
use App\Services\AvailabilityService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function show(Request $request, Business $business, AvailabilityService $availability): View
    {
        $dateValue = (string) $request->query('date', now($business->timezone)->toDateString());
        Validator::make(['date' => $dateValue], ['date' => ['required', 'date_format:Y-m-d']])->validate();
        $date = CarbonImmutable::createFromFormat('!Y-m-d', $dateValue, $business->timezone);

        $services = $business->services()->where('is_active', true)->orderBy('name')->get();
        $selectedService = $services->firstWhere('id', (int) $request->query('service_id')) ?? $services->first();
        $slots = $selectedService ? $availability->slots($business, $selectedService, $date) : [];

        return view('booking.show', compact('business', 'services', 'selectedService', 'dateValue', 'slots'));
    }

    public function store(Request $request, Business $business, AvailabilityService $availability): RedirectResponse
    {
        $data = $request->validate([
            'service_id' => ['required', 'integer'],
            'date' => ['required', 'date_format:Y-m-d'],
            'time' => ['required', 'date_format:H:i'],
            'customer_name' => ['required', 'string', 'max:120'],
            'customer_email' => ['required', 'email', 'max:180'],
            'customer_phone' => ['nullable', 'string', 'max:40'],
            'customer_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $localDate = CarbonImmutable::createFromFormat('!Y-m-d', $data['date'], $business->timezone);
        if ($localDate->lt(CarbonImmutable::today($business->timezone))) {
            throw ValidationException::withMessages(['date' => 'Geçmiş bir tarihe randevu alınamaz.']);
        }

        $appointment = DB::transaction(function () use ($business, $data, $localDate, $availability) {
            $lockedBusiness = Business::query()->whereKey($business->id)->lockForUpdate()->firstOrFail();
            $service = $lockedBusiness->services()
                ->whereKey($data['service_id'])
                ->where('is_active', true)
                ->firstOrFail();

            $availableSlots = $availability->slots($lockedBusiness, $service, $localDate);
            if (! in_array($data['time'], $availableSlots, true)) {
                throw ValidationException::withMessages(['time' => 'Bu saat artık uygun değil. Lütfen başka bir saat seç.']);
            }

            $startsAt = $localDate->setTimeFromTimeString($data['time']);
            $endsAt = $startsAt->addMinutes((int) $service->duration_minutes);

            return Appointment::create([
                'business_id' => $lockedBusiness->id,
                'service_id' => $service->id,
                'customer_name' => $data['customer_name'],
                'customer_email' => $data['customer_email'],
                'customer_phone' => $data['customer_phone'] ?? null,
                'customer_note' => $data['customer_note'] ?? null,
                'starts_at' => $startsAt->utc(),
                'ends_at' => $endsAt->utc(),
                'status' => 'pending',
            ]);
        }, attempts: 3);

        return redirect()->route('booking.confirmation', [$business, $appointment->id]);
    }

    public function confirmation(Business $business, int $appointmentId): View
    {
        $appointment = $business->appointments()->with('service')->findOrFail($appointmentId);

        return view('booking.confirmation', compact('business', 'appointment'));
    }
}
