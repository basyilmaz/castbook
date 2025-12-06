# CastBook API Dokümantasyonu

CastBook, dahili API endpoint'leri üzerinden veri alışverişi sağlar. Bu dokümantasyon, mevcut API endpoint'lerini ve kullanımlarını açıklar.

## Kimlik Doğrulama

Tüm API istekleri oturum tabanlı kimlik doğrulama gerektirir. AJAX istekleri için CSRF token zorunludur.

### CSRF Token

Her istek için `X-CSRF-TOKEN` header'ı eklenmelidir:

```javascript
fetch('/api/endpoint', {
    method: 'POST',
    headers: {
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
        'X-Requested-With': 'XMLHttpRequest'
    }
});
```

---

## Firma API'ları

### Fatura Fiyatlarını Getir

```http
GET /firms/{firm_id}/invoice-prices?date=YYYY-MM-DD
```

**Parametreler:**
| Parametre | Tip | Zorunlu | Açıklama |
|-----------|-----|---------|----------|
| firm_id | integer | Evet | Firma ID |
| date | string | Hayır | Tarih (YYYY-MM-DD) |

**Yanıt:**
```json
{
    "monthly_fee": 1500.00,
    "stamp_tax": 35.00
}
```

---

## Fatura API'ları

### Fatura Listesi

```http
GET /invoices?status=unpaid&firm_id=1&date_from=2024-01-01&date_to=2024-12-31
```

**Filtre Parametreleri:**
| Parametre | Tip | Açıklama |
|-----------|-----|----------|
| status | string | unpaid, partial, paid, cancelled |
| firm_id | integer | Firmaya göre filtrele |
| date_from | string | Başlangıç tarihi |
| date_to | string | Bitiş tarihi |
| per_page | integer | Sayfa başı kayıt (10, 20, 50, 100) |

### Toplu Durum Güncelleme

```http
PATCH /invoices/bulk-status
```

**İstek Gövdesi:**
```json
{
    "ids": "1,2,3,4,5",
    "status": "paid"
}
```

**Yanıt:**
```json
{
    "success": true,
    "message": "5 faturanın durumu güncellendi."
}
```

---

## Tahsilat API'ları

### Firma Bazlı Ödenmemiş Faturalar

```http
GET /invoices/outstanding?firm_id=1
```

**Yanıt:**
```json
[
    {
        "id": 123,
        "official_number": "FA-2024-001",
        "amount": 1500.00,
        "remaining_amount": 750.00,
        "date": "2024-01-15",
        "due_date": "2024-02-15"
    }
]
```

---

## Beyanname API'ları

### Hızlı Durum Güncelleme

```http
PATCH /tax-declarations/{id}/status
```

**İstek Gövdesi:**
```json
{
    "status": "filed"
}
```

**Geçerli Durumlar:**
- `pending` - Bekliyor
- `filed` - Beyan edildi
- `paid` - Ödendi
- `not_required` - Gerekli değil

**Yanıt:**
```json
{
    "success": true,
    "message": "Durum güncellendi.",
    "declaration": {
        "id": 1,
        "status": "filed",
        "filed_at": "2024-12-05T10:30:00Z"
    }
}
```

---

## Bildirim API'ları

### Bildirimleri Getir

```http
GET /notifications/list
```

**Yanıt:**
```json
{
    "notifications": [
        {
            "id": 1,
            "title": "Yeni Fatura",
            "message": "Firma A için fatura oluşturuldu",
            "type": "info",
            "read_at": null,
            "created_at": "2024-12-05T09:00:00Z"
        }
    ],
    "unread_count": 3
}
```

### Bildirimi Okundu İşaretle

```http
POST /notifications/{id}/read
```

### Tümünü Okundu İşaretle

```http
POST /notifications/read-all
```

---

## Arama API'ları

### Global Arama

```http
GET /search?q=arama_terimi
```

**Yanıt:**
```json
{
    "firms": [
        {"id": 1, "name": "ABC Ltd.", "url": "/firms/1"}
    ],
    "invoices": [
        {"id": 123, "official_number": "FA-2024-001", "url": "/invoices/123"}
    ]
}
```

---

## Raporlar

### Bakiye Raporu Export

```http
GET /reports/balances/export?status=active&format=csv
```

### Tahsilat Raporu Export

```http
GET /reports/collections/export?year=2024&format=pdf
```

### Fatura Raporu Export

```http
GET /reports/invoices/export?status=unpaid&format=pdf
```

---

## Hata Yanıtları

### 401 Unauthorized
```json
{
    "error": "Unauthorized",
    "message": "Oturum açmanız gerekiyor."
}
```

### 403 Forbidden
```json
{
    "error": "Forbidden",
    "message": "Bu işlem için yetkiniz yok."
}
```

### 404 Not Found
```json
{
    "error": "Not Found",
    "message": "Kayıt bulunamadı."
}
```

### 422 Validation Error
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "status": ["Durum alanı gereklidir."]
    }
}
```

### 429 Too Many Requests
```json
{
    "error": "Too Many Requests",
    "message": "Çok fazla istek gönderildi. Lütfen bekleyin."
}
```

---

## Rate Limiting

API istekleri rate limiting'e tabidir:

| Endpoint | Limit |
|----------|-------|
| Login | 5/dakika |
| Şifre sıfırlama | 3/dakika |
| Genel API | 60/dakika |
| Form gönderimi | 30/dakika |

---

## Örnek Kullanımlar

### JavaScript (Fetch API)

```javascript
// Fatura durumunu güncelle
async function updateInvoiceStatus(ids, status) {
    const response = await fetch('/invoices/bulk-status', {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
        },
        body: JSON.stringify({ ids: ids.join(','), status })
    });
    
    return response.json();
}
```

### jQuery AJAX

```javascript
$.ajax({
    url: '/notifications/read-all',
    method: 'POST',
    headers: {
        'X-CSRF-TOKEN': $('meta[name=csrf-token]').attr('content')
    },
    success: function(data) {
        console.log('Tüm bildirimler okundu');
    }
});
```
