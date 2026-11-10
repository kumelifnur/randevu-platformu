@extends('layouts.app', ['title' => $business->name.' — Randevu al'])

@section('content')
<section class="booking-hero"><p class="eyebrow">RANDEVU SAYFASI</p><h1>{{ $business->name }}</h1><p class="lead">{{ $business->description ?: 'Hizmet seç, sana uyan zamanı bul ve randevu talebini gönder.' }}</p><div class="timezone-note">Saatler {{ $business->timezone }} saat dilimindedir.</div></section>

<section class="booking-layout">
    <div class="panel booking-panel">
        <div class="section-heading"><div><span class="step-number">01</span><h2>Hizmet ve tarih seç</h2><p>Uygun saatleri görmek için seçimlerini yap.</p></div></div>
        <form method="GET" action="{{ route('booking.show', $business) }}" class="filter-form">
            <label>Hizmet<select name="service_id" required>@forelse($services as $service)<option value="{{ $service->id }}" @selected($selectedService?->id === $service->id)>{{ $service->name }} · {{ $service->duration_minutes }} dk</option>@empty<option value="">Henüz hizmet yok</option>@endforelse</select></label>
            <label>Tarih<input type="date" name="date" value="{{ $dateValue }}" min="{{ now($business->timezone)->toDateString() }}" required></label>
            <button class="button secondary" type="submit">Saatleri göster</button>
        </form>
        @if($selectedService)
            <div class="slot-section"><h3>{{ $dateValue }} için uygun saatler</h3>
                @if($slots)
                    <div class="slot-grid">@foreach($slots as $slot)<label class="slot-choice"><input type="radio" form="booking-form" name="time" value="{{ $slot }}" @checked($loop->first) required><span>{{ $slot }}</span></label>@endforeach</div>
                @else
                    <div class="empty-inline">Bu tarihte uygun saat bulunamadı. Başka bir tarih seçebilirsin.</div>
                @endif
            </div>
        @endif
    </div>

    @if($selectedService && $slots)
        <form id="booking-form" class="panel booking-panel customer-form" method="POST" action="{{ route('booking.store', $business) }}">
            @csrf
            <input type="hidden" name="service_id" value="{{ $selectedService->id }}">
            <input type="hidden" name="date" value="{{ $dateValue }}">
            <div class="section-heading"><div><span class="step-number">02</span><h2>İletişim bilgilerini gir</h2><p>Randevu talebin işletmeye iletilecek.</p></div></div>
            <label>Adın<input name="customer_name" value="{{ old('customer_name') }}" required maxlength="120" autocomplete="name"></label>
            <label>E-posta<input name="customer_email" type="email" value="{{ old('customer_email') }}" required maxlength="180" autocomplete="email"></label>
            <label>Telefon <span class="muted">(isteğe bağlı)</span><input name="customer_phone" type="tel" value="{{ old('customer_phone') }}" maxlength="40" autocomplete="tel"></label>
            <label>Not <span class="muted">(isteğe bağlı)</span><textarea name="customer_note" rows="3" maxlength="1000">{{ old('customer_note') }}</textarea></label>
            <button class="button primary full" type="submit">Randevu talebi gönder <span>↗</span></button>
            <p class="privacy-note">Gönderdiğin bilgiler yalnızca bu randevuyu yönetmek için işletme sahibine gösterilir.</p>
        </form>
    @endif
</section>
@if(! $services->count())<div class="empty-inline">Bu işletme henüz randevuya açık hizmet eklememiş.</div>@endif
@endsection
