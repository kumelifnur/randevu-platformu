<?php

namespace App\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class BusinessHoursController extends Controller
{
    private const DAYS = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];

    public function update(Request $request): RedirectResponse
    {
        $business = $request->user()->business()->firstOrFail();
        $data = $request->validate([
            'hours' => ['required', 'array', 'size:7'],
            'hours.*.is_closed' => ['required', 'boolean'],
            'hours.*.opens_at' => ['nullable', 'date_format:H:i'],
            'hours.*.closes_at' => ['nullable', 'date_format:H:i'],
        ]);

        foreach (range(0, 6) as $weekday) {
            $row = $data['hours'][$weekday] ?? null;
            if (! $row) {
                throw ValidationException::withMessages(['hours' => 'Haftanın her günü için bir ayar gönderilmelidir.']);
            }

            $closed = (bool) $row['is_closed'];
            $opensAt = $closed ? null : ($row['opens_at'] ?? null);
            $closesAt = $closed ? null : ($row['closes_at'] ?? null);

            if (! $closed && (! $opensAt || ! $closesAt || CarbonImmutable::parse($opensAt)->gte(CarbonImmutable::parse($closesAt)))) {
                throw ValidationException::withMessages([
                    "hours.$weekday.closes_at" => self::DAYS[$weekday].' için kapanış saati açılış saatinden sonra olmalıdır.',
                ]);
            }

            $business->hours()->updateOrCreate(
                ['weekday' => $weekday],
                ['opens_at' => $opensAt, 'closes_at' => $closesAt, 'is_closed' => $closed],
            );
        }

        return back()->with('success', 'Çalışma saatleri güncellendi.');
    }
}
