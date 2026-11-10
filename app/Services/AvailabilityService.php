<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Business;
use App\Models\Service;
use Carbon\CarbonImmutable;

class AvailabilityService
{
    /** @return list<string> Available local start times in H:i format. */
    public function slots(Business $business, Service $service, CarbonImmutable $date): array
    {
        $date = $date->setTimezone($business->timezone)->startOfDay();
        $hours = $business->hours()->where('weekday', $date->dayOfWeek)->first();

        if (! $hours || $hours->is_closed || ! $hours->opens_at || ! $hours->closes_at) {
            return [];
        }

        $opensAt = CarbonImmutable::parse($date->toDateString().' '.$hours->opens_at, $business->timezone);
        $closesAt = CarbonImmutable::parse($date->toDateString().' '.$hours->closes_at, $business->timezone);
        $duration = (int) $service->duration_minutes;

        $busyAppointments = $business->appointments()
            ->where('status', '!=', 'cancelled')
            ->where('starts_at', '<', $date->endOfDay()->utc())
            ->where('ends_at', '>', $date->startOfDay()->utc())
            ->get(['starts_at', 'ends_at']);

        $slots = [];
        $step = max(5, (int) $business->slot_interval_minutes);
        $now = CarbonImmutable::now($business->timezone);

        for ($slot = $opensAt; $slot->addMinutes($duration)->lte($closesAt); $slot = $slot->addMinutes($step)) {
            $slotEnd = $slot->addMinutes($duration);
            if ($slot->lte($now)) {
                continue;
            }

            $overlaps = $busyAppointments->contains(function (Appointment $appointment) use ($slot, $slotEnd): bool {
                return $slot->utc()->lt($appointment->ends_at) && $slotEnd->utc()->gt($appointment->starts_at);
            });

            if (! $overlaps) {
                $slots[] = $slot->format('H:i');
            }
        }

        return $slots;
    }
}
