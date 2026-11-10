@extends('layouts.app', ['title' => 'Randevu talebin alındı'])

@section('content')
<section class="confirmation-wrap"><div class="confirmation-mark">✓</div><p class="eyebrow">TALEBİN ALINDI</p><h1>Randevu isteğin<br><em>işletmeye iletildi.</em></h1><p class="lead">İşletme randevunu onayladığında seninle iletişime geçecek.</p><article class="confirmation-card"><div><span>İŞLETME</span><strong>{{ $business->name }}</strong></div><div><span>HİZMET</span><strong>{{ $appointment->service->name }}</strong></div><div><span>TARİH VE SAAT</span><strong>{{ $appointment->starts_at->setTimezone($business->timezone)->format('d.m.Y · H:i') }}</strong></div><div><span>DURUM</span><strong class="status status-pending">Onay bekliyor</strong></div></article><a class="button secondary" href="{{ route('booking.show', $business) }}">İşletme sayfasına dön</a></section>
@endsection
