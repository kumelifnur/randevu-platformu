<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\Service;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'owner_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:180', 'unique:users,email'],
            'business_name' => ['required', 'string', 'max:120'],
            'password' => ['required', 'string', 'min:10', 'confirmed'],
        ]);

        $user = DB::transaction(function () use ($data) {
            $user = \App\Models\User::create([
                'name' => $data['owner_name'],
                'email' => $data['email'],
                'password' => $data['password'],
            ]);

            $baseSlug = Str::slug($data['business_name']) ?: 'isletme';
            $slug = $baseSlug;
            $suffix = 2;
            while (Business::where('slug', $slug)->exists()) {
                $slug = $baseSlug.'-'.$suffix++;
            }

            $business = Business::create([
                'owner_id' => $user->id,
                'name' => $data['business_name'],
                'slug' => $slug,
                'timezone' => 'Europe/Istanbul',
                'slot_interval_minutes' => 15,
            ]);

            foreach (range(0, 6) as $weekday) {
                $business->hours()->create([
                    'weekday' => $weekday,
                    'opens_at' => $weekday === 0 ? null : '09:00',
                    'closes_at' => $weekday === 0 ? null : '17:00',
                    'is_closed' => $weekday === 0,
                ]);
            }

            Service::create([
                'business_id' => $business->id,
                'name' => 'Genel randevu',
                'description' => 'İşletme panelinden bu hizmeti düzenleyebilirsiniz.',
                'duration_minutes' => 30,
                'is_active' => true,
            ]);

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'İşletmen hazır. Hizmet ve çalışma saatlerini düzenleyebilirsin.');
    }
}
