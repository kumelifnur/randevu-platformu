@extends('layouts.app', ['title' => 'Randevu — Randevuları kolayca yönet'])

@section('content')
<section class="hero">
    <div class="hero-copy">
        <p class="eyebrow">İŞLETMELER İÇİN RANDEVU TAKVİMİ</p>
        <h1>Takvimin sana<br><em>zaman kazandırsın.</em></h1>
        <p class="lead">Hizmetlerini tanımla, çalışma saatlerini ayarla ve müşterilerin uygun zamanı kendileri seçsin.</p>
        <div class="hero-actions">
            <a class="button primary" href="{{ route('register') }}">Ücretsiz işletme hesabı aç <span>↗</span></a>
            <a class="text-link" href="{{ route('login') }}">Hesabım var</a>
        </div>
        <div class="hero-points"><span>✓ Çakışan randevu kontrolü</span><span>✓ İşletmeye özel sayfa</span></div>
    </div>
    <div class="hero-art" aria-hidden="true">
        <div class="art-orbit orbit-one"></div><div class="art-orbit orbit-two"></div>
        <div class="calendar-card"><div class="calendar-top"><span>HAFTALIK TAKVİM</span><b>⌁</b></div><div class="calendar-title">Kasım <span>2026</span></div><div class="calendar-days"><i>Pzt</i><i>Sal</i><i>Çar</i><i>Per</i><i>Cum</i></div><div class="calendar-dates"><b>9</b><b>10</b><b class="selected">11</b><b>12</b><b>13</b></div><div class="mini-event"><i></i><span><b>Danışmanlık</b><small>11:30 · 30 dakika</small></span><strong>Onaylandı</strong></div><div class="mini-event"><i class="sage"></i><span><b>İlk görüşme</b><small>14:00 · 45 dakika</small></span><strong>Yeni</strong></div></div>
        <div class="floating-note"><span>✳</span><b>Takvim hazır</b><small>Uygun saatler otomatik</small></div>
    </div>
</section>
<section class="feature-row"><article><span>01</span><h2>İşletmeni kur</h2><p>Hizmetlerini ve haftalık çalışma saatlerini tek yerden yönet.</p></article><article><span>02</span><h2>Bağlantını paylaş</h2><p>Sana özel sayfada müşteriler hizmet ve uygun saat seçsin.</p></article><article><span>03</span><h2>Takvimi düzenle</h2><p>Randevuları onayla, iptal et veya tamamlandı olarak işaretle.</p></article></section>
@endsection
