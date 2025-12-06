# Backlog Planı ve Öncelikler

## Acil Aksiyon Planı (0-3 Ay)
| Öncelik | İş Paketi | Açıklama | Sahip | Durum |
| --- | --- | --- | --- | --- |
| P0 | JSON yedek geri yükleme sertleştirmesi | Dry-run modu, satır sayımı raporu ve schema checksum doğrulaması; geri yükleme öncesi kullanıcı onayı ve rapor ekranı | Backend | Planlandı |
| P0 | Incremental/delta backup restore | Tam tablo truncate yerine delta karşılaştırma ve kademeli uygulama stratejisi | Backend | Açık |
| P0 | Kısmi ödeme UX denetimi | Yeni `partial` akışının uyarı banner’ı, açık bakiye metriği ve rapor filtreleri ile doğrulanması | Ürün + QA | Devam ediyor |
| P0 | Rol/izin altyapısı başlatma | Rol tablosu migrasyonu, middleware, mevcut admin kullanıcı geçişi | Backend | Hazırlık |
| P1 | Audit log temel altyapısı | Login, fatura/tahsilat silme, ayar güncelleme olaylarını kayıt altına alma | Backend | Planlandı |
| P1 | Rapor performans optimizasyonu | Tüm raporlara sayfalama + özet sorgular; CSV/Excel export | Backend | Planlandı |
| P1 | Scheduler health check & uyarı | `app:generate-monthly-invoices` komutu için health endpoint + Slack/E-posta uyarıları | DevOps | Planlandı |

## Yetki Modeli Genişlemesi
| Öncelik | İş Paketi | Açıklama | Bağımlılıklar | Tahmini Süre |
| --- | --- | --- | --- | --- |
| P0 | Rol & izin altyapısı | `roles` tablosu, pivot, seed güncellemesi | Database migrasyonu | 2 gün |
| P0 | Policy/Permission entegrasyonu | Modül bazlı izin matrisi, middleware tanımı | Rol altyapısı | 3 gün |
| P1 | Kullanıcı yönetimi UI genişletmesi | Rol atama, parola sıfırlama, davet linki | Rol/izin tamamlanmış olmalı | 2 gün |
| P1 | Audit & oturum kayıtları | Login log, kritik işlem kaydı | Rol/izin tamamlanmış olmalı | 2 gün |
| P2 | Toplu kullanıcı import/export | CSV şablonu + validasyon | Kullanıcı UI | 3 gün |

## E-Fatura / E-Arşiv Entegrasyonu
| Öncelik | İş Paketi | Açıklama | Bağımlılıklar | Tahmini Süre |
| --- | --- | --- | --- | --- |
| P0 | Entegratör seçimi & sözleşme | GİB uyumlu sağlayıcı seçimi, API erişimi | - | 1 hafta (iş) |
| P0 | Fatura veri modeli genişletme | UBL alanları, mali mühür/UUID saklama | Entegratör seçimi | 3 gün |
| P1 | API istemcisi & gönderim flow | Taslak oluşturma, API çağrısı, hata yönetimi | Veri modeli | 5 gün |
| P1 | Webhook/geri bildirim servisi | Entegratörden gelen yanıtların işlenmesi | API istemcisi | 3 gün |
| P1 | UI güncellemeleri | Fatura durum rozetleri, toplu gönderim ekranı | API akışı | 3 gün |
| P2 | Test & sertifikasyon | GİB test ortamı, entegratör checklist | Tüm teknik işler | 1 hafta |

## Güvenlik Yol Haritası
| Öncelik | Kalem | Açıklama |
| --- | --- | --- |
| P0 | Rate limiting | Login, parola sıfırlama ve kritik POST uçları için throttling |
| P0 | Yedek geri yükleme şifreleme | JSON backup şifresi + checksum doğrulaması, isteğe bağlı GPG |
| P1 | Audit log | Kritik CRUD ve ayar değişimlerini loglama |
| P1 | Scheduler alerting | Başarısız artisan görevlerinde Slack/E-posta alarmı |
| P2 | 2FA | Yönetici rolü için TOTP tabanlı MFA |
| P2 | Dosya güvenliği | Logo/files için AV taraması ve imza doğrulama |

Not: Öncelikler P0 (kritik), P1 (yüksek), P2 (orta) olarak değerlendirilmiştir.
