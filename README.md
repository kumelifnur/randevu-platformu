# Randevu

Küçük işletmelerin çevrimiçi randevu alması ve takvimlerini yönetmesi için Laravel uygulaması.

## Özellikler

- İşletme sahibi hesabı açma ve oturum yönetimi
- İşletmeye özel, paylaşılabilir rezervasyon sayfası
- Hizmet, süre, fiyat ve haftalık çalışma saatlerini yönetme
- Müşteri için uygun saatleri hizmet süresine göre hesaplama
- Randevu çakışmalarını kontrol ederek talep oluşturma
- Randevu onaylama, iptal etme ve tamamlandı olarak işaretleme
- İşletme verilerini işletme sahibine göre kapsamlandırma
- SQLite ile yerel geliştirme

## Gereksinimler

- PHP 8.2 veya üzeri
- Composer
- SQLite PDO eklentisi

## Yerel kurulum

Proje klasöründe PowerShell ile:

```powershell
composer install
Copy-Item .env.example .env
./start-local.ps1
```

Başlangıç betiği SQLite eklentilerini açar, uygulama anahtarını ve yerel veritabanını hazırlar, migrasyonları çalıştırır ve geliştirme sunucusunu başlatır. Uygulamayı `http://127.0.0.1:8000` adresinde aç. **İşletmeni ekle** sayfasından hesap oluşturduğunda bir işletme, örnek hizmet ve başlangıç çalışma saatleri hazırlanır. İşletme panelinden bunları düzenleyebilirsin.

Müşteri sayfası `http://127.0.0.1:8000/r/isletme-adin` biçiminde oluşturulur. Yeni randevular ilk olarak **Onay bekliyor** durumunda görünür.

## Zamanlama ve veritabanı

Randevu oluşturulurken uygunluk işlem içinde tekrar hesaplanır ve çakışma kontrolü yapılır. Eşzamanlı isteklerde satır kilitlemesinden yararlanmak için canlı ortamda MySQL veya PostgreSQL kullanılması önerilir. SQLite yerel geliştirme içindir.
