@extends('layouts.app', ['title' => $business->name.' — İşletme paneli'])

@section('content')
@php
    $dayNames = ['Pazar', 'Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma', 'Cumartesi'];
    $hoursByDay = $business->hours->keyBy('weekday');
    $statusNames = ['pending' => 'Onay bekliyor', 'confirmed' => 'Onaylandı', 'cancelled' => 'İptal edildi', 'completed' => 'Tamamlandı'];
@endphp
<section class="dashboard-head">
    <div><p class="eyebrow">İŞLETME PANELİ</p><h1>{{ $business->name }}</h1><p class="muted">İşletme bağlantın: <a href="{{ route('booking.show', $business) }}" target="_blank">{{ url(route('booking.show', $business, false)) }}</a></p></div>
    <a class="button secondary" href="{{ route('booking.show', $business) }}" target="_blank">Müşteri sayfasını gör ↗</a>
</section>
<section class="stat-grid"><article class="stat-card"><span>Bugünün randevuları</span><strong>{{ $todayCount }}</strong><small>Onay bekleyen ve onaylı</small></article><article class="stat-card"><span>Önümüzdeki 7 gün</span><strong>{{ $weekCount }}</strong><small>Onay bekleyen ve onaylı</small></article><article class="stat-card"><span>Aktif hizmetler</span><strong>{{ $business->services->where('is_active', true)->count() }}</strong><small>Rezervasyon sayfanda görünür</small></article></section>

<section class="dashboard-grid">
    <div class="panel wide-panel">
        <div class="section-heading"><div><h2>Yaklaşan randevular</h2><p>Yeni randevuları onaylayabilir veya iptal edebilirsin.</p></div></div>
        @forelse($appointments as $appointment)
            <article class="appointment-row">
                <div class="appointment-date"><b>{{ $appointment->starts_at->setTimezone($business->timezone)->format('d') }}</b><span>{{ $appointment->starts_at->setTimezone($business->timezone)->format('m.Y') }}</span></div>
                <div class="appointment-main"><strong>{{ $appointment->customer_name }}</strong><span>{{ $appointment->service->name }} · {{ $appointment->starts_at->setTimezone($business->timezone)->format('H:i') }}</span><small><a href="mailto:{{ $appointment->customer_email }}">{{ $appointment->customer_email }}</a>@if($appointment->customer_phone) · {{ $appointment->customer_phone }}@endif</small></div>
                <span class="status status-{{ $appointment->status }}">{{ $statusNames[$appointment->status] ?? $appointment->status }}</span>
                <div class="row-actions">
                    @if($appointment->status === 'pending')
                        <form method="POST" action="{{ route('appointments.status', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="confirmed"><button class="small-action confirm" type="submit">Onayla</button></form>
                        <form method="POST" action="{{ route('appointments.status', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="cancelled"><button class="small-action" type="submit">İptal</button></form>
                    @elseif($appointment->status === 'confirmed')
                        <form method="POST" action="{{ route('appointments.status', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="completed"><button class="small-action confirm" type="submit">Tamamlandı</button></form>
                        <form method="POST" action="{{ route('appointments.status', $appointment) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="cancelled"><button class="small-action" type="submit">İptal</button></form>
                    @endif
                </div>
            </article>
        @empty
            <div class="empty-state"><span>⌁</span><b>Henüz yaklaşan randevu yok</b><p>Rezervasyon sayfanı paylaşınca yeni talepler burada görünür.</p></div>
        @endforelse
        <div class="pagination">{{ $appointments->links() }}</div>
    </div>

    <div class="panel side-panel">
        <div class="section-heading"><div><h2>Hizmetlerin</h2><p>Müşterilerin randevu alacağı seçenekler.</p></div></div>
        <div class="service-list">
            @forelse($business->services as $service)
                <div class="service-row"><div><b>{{ $service->name }}</b><small>{{ $service->duration_minutes }} dakika @if($service->price !== null) · ₺{{ number_format((float) $service->price, 2, ',', '.') }} @else · Fiyat belirtilmedi @endif @unless($service->is_active) · Arşivlendi @endunless</small></div><details class="service-edit"><summary>Düzenle</summary><form class="service-edit-form" method="POST" action="{{ route('services.update', $service) }}">@csrf @method('PUT')<label>Hizmet adı<input name="name" value="{{ $service->name }}" required maxlength="120"></label><div class="form-two"><label>Süre (dk)<input name="duration_minutes" type="number" value="{{ $service->duration_minutes }}" min="15" max="240" step="15" required></label><label>Fiyat (₺)<input name="price" type="number" min="0" step="0.01" value="{{ $service->price }}"></label></div><label>Açıklama<textarea name="description" maxlength="600" rows="2">{{ $service->description }}</textarea></label><button class="button secondary full" type="submit">Kaydet</button></form></details><form method="POST" action="{{ route('services.destroy', $service) }}" onsubmit="return confirm('Bu hizmeti arşivlemek istiyor musun?')">@csrf @method('DELETE')<button class="icon-button" aria-label="Hizmeti arşivle" title="Hizmeti arşivle">×</button></form></div>
            @empty <p class="muted">Henüz hizmet eklenmedi.</p> @endforelse
        </div>
        <form class="compact-form" method="POST" action="{{ route('services.store') }}">
            @csrf<h3>Yeni hizmet ekle</h3>
            <label>Hizmet adı<input name="name" value="{{ old('name') }}" required maxlength="120" placeholder="Örn. İlk danışmanlık"></label>
            <div class="form-two"><label>Süre (dk)<input name="duration_minutes" type="number" value="{{ old('duration_minutes', 30) }}" min="15" max="240" step="15" required></label><label>Fiyat (₺)<input name="price" type="number" min="0" step="0.01" value="{{ old('price') }}" placeholder="İsteğe bağlı"></label></div>
            <label>Kısa açıklama<textarea name="description" maxlength="600" rows="2" placeholder="Hizmet hakkında kısa bilgi">{{ old('description') }}</textarea></label>
            <button class="button secondary full" type="submit">Hizmeti ekle</button>
        </form>
    </div>

    <div class="panel hours-panel">
        <div class="section-heading"><div><h2>Çalışma saatleri</h2><p>Uygunluk, hizmet süresine göre hesaplanır. Saat dilimi: {{ $business->timezone }}.</p></div></div>
        <form method="POST" action="{{ route('hours.update') }}">
            @csrf @method('PUT')
            <div class="hours-table">
                @foreach($dayNames as $weekday => $dayName)
                    @php $hour = $hoursByDay->get($weekday); @endphp
                    <div class="hours-row"><strong>{{ $dayName }}</strong><label class="closed-select"><span class="sr-only">{{ $dayName }} açık mı?</span><select name="hours[{{ $weekday }}][is_closed]"><option value="0" @selected($hour && ! $hour->is_closed)>Açık</option><option value="1" @selected(! $hour || $hour->is_closed)>Kapalı</option></select></label><label><span class="sr-only">{{ $dayName }} açılış saati</span><input type="time" name="hours[{{ $weekday }}][opens_at]" value="{{ $hour?->opens_at ? substr($hour->opens_at, 0, 5) : '09:00' }}"></label><span class="time-separator">—</span><label><span class="sr-only">{{ $dayName }} kapanış saati</span><input type="time" name="hours[{{ $weekday }}][closes_at]" value="{{ $hour?->closes_at ? substr($hour->closes_at, 0, 5) : '17:00' }}"></label></div>
                @endforeach
            </div>
            <button class="button secondary" type="submit">Saatleri kaydet</button>
        </form>
    </div>
</section>
@endsection
