@extends('layouts.app')

@php
    use App\Support\Format;

    $currentPrice = $firm->priceForDate(now());
    $balance = $firm->balance;
    
    // Firma durumu analizi
    $hasOverdue = $firm->invoices()
        ->whereIn('status', ['unpaid', 'partial'])
        ->where('due_date', '<', now())
        ->exists();
    $pendingDeclarations = $firm->taxDeclarations()
        ->where('status', 'pending')
        ->where('due_date', '>=', now())
        ->where('due_date', '<=', now()->addDays(7))
        ->count();
    $unpaidInvoiceCount = $firm->invoices()->whereIn('status', ['unpaid', 'partial'])->count();
    
    // Durum belirleme
    if ($firm->status !== 'active') {
        $firmStatusClass = 'secondary';
        $firmStatusIcon = 'pause-circle';
        $firmStatusText = 'Pasif Firma';
    } elseif ($hasOverdue) {
        $firmStatusClass = 'danger';
        $firmStatusIcon = 'exclamation-triangle';
        $firmStatusText = 'Gecikmiş Ödeme Var';
    } elseif ($balance > 0) {
        $firmStatusClass = 'warning';
        $firmStatusIcon = 'clock-history';
        $firmStatusText = 'Bakiye Bekliyor';
    } else {
        $firmStatusClass = 'success';
        $firmStatusIcon = 'check-circle';
        $firmStatusText = 'Tüm Ödemeler Tamam';
    }
@endphp

@section('content')
<div class="container py-4">
    {{-- Firma Hero Header --}}
    <div class="card border-0 shadow-sm mb-4 overflow-hidden">
        <div class="firm-header-gradient">
            <div class="row align-items-center">
                <div class="col-lg-7">
                    <div class="p-4">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="firm-avatar">
                                {{ mb_strtoupper(mb_substr($firm->name, 0, 2)) }}
                            </div>
                            <div>
                                <h3 class="text-white mb-1 fw-bold">{{ $firm->name }}</h3>
                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    <span class="badge bg-{{ $firmStatusClass }}">
                                        <i class="bi bi-{{ $firmStatusIcon }} me-1"></i>{{ $firmStatusText }}
                                    </span>
                                    @if($firm->company_type)
                                    <span class="badge bg-light text-dark">
                                        {{ $firm->company_type->label() }}
                                    </span>
                                    @endif
                                    @if($firm->tax_no)
                                    <span class="text-white-50 small">
                                        <i class="bi bi-upc me-1"></i>VKN: {{ $firm->tax_no }}
                                    </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        {{-- Hızlı İşlemler --}}
                        <div class="d-flex flex-wrap gap-2">
                            <a href="{{ route('invoices.create', ['firm_id' => $firm->id]) }}" class="btn btn-light btn-sm">
                                <i class="bi bi-plus-lg me-1"></i>Fatura Oluştur
                            </a>
                            <a href="{{ route('payments.create', ['firm_id' => $firm->id]) }}" class="btn btn-success btn-sm">
                                <i class="bi bi-cash me-1"></i>Tahsilat Ekle
                            </a>
                            <a href="{{ route('firms.declarations', $firm) }}" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-file-earmark-text me-1"></i>Beyannameler
                            </a>
                            <a href="{{ route('firms.edit', $firm) }}" class="btn btn-outline-light btn-sm">
                                <i class="bi bi-pencil me-1"></i>Düzenle
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-lg-5">
                    <div class="p-4">
                        <div class="row text-center g-3">
                            <div class="col-4">
                                <div class="firm-stat-box">
                                    <div class="firm-stat-value {{ $balance > 0 ? 'text-warning' : 'text-white' }}">
                                        {{ Format::money($balance) }}
                                    </div>
                                    <div class="firm-stat-label">Güncel Bakiye</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="firm-stat-box">
                                    <div class="firm-stat-value text-white">{{ Format::money($currentPrice) }}</div>
                                    <div class="firm-stat-label">Aylık Ücret</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="firm-stat-box">
                                    <div class="firm-stat-value text-white">{{ $unpaidInvoiceCount }}</div>
                                    <div class="firm-stat-label">Bekleyen Fatura</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- İkincil İşlemler Bar --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
        <div class="d-flex flex-wrap gap-2">
            <span class="fw-semibold text-muted">Diğer İşlemler:</span>
            <form action="{{ route('firms.sync-invoices', $firm) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-warning">
                    <i class="bi bi-arrow-repeat me-1"></i>Geçmiş Borçları Senkronize Et
                </button>
            </form>
        </div>
        <div>
            @if($firm->contract_start_at)
            <small class="text-muted">
                <i class="bi bi-calendar3 me-1"></i>Sözleşme: {{ $firm->contract_start_at->format('d.m.Y') }}
            </small>
            @endif
        </div>
    </div>

    <div class="row g-4">
        {{-- Sol Kolon --}}
        <div class="col-lg-4">
            {{-- İletişim Bilgileri --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex align-items-center gap-2">
                    <i class="bi bi-person-lines-fill text-primary"></i>
                    <h6 class="mb-0">İletişim Bilgileri</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex flex-column gap-3">
                        @if($firm->contact_person)
                        <div class="d-flex align-items-center gap-3">
                            <div class="contact-icon bg-primary bg-opacity-10">
                                <i class="bi bi-person text-primary"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Yetkili</small>
                                <span class="fw-medium">{{ $firm->contact_person }}</span>
                            </div>
                        </div>
                        @endif
                        
                        @if($firm->contact_phone)
                        <div class="d-flex align-items-center gap-3">
                            <div class="contact-icon bg-success bg-opacity-10">
                                <i class="bi bi-telephone text-success"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Telefon</small>
                                <a href="tel:{{ $firm->contact_phone }}" class="fw-medium text-decoration-none">
                                    {{ $firm->contact_phone }}
                                </a>
                            </div>
                        </div>
                        @endif
                        
                        @if($firm->contact_email)
                        <div class="d-flex align-items-center gap-3">
                            <div class="contact-icon bg-info bg-opacity-10">
                                <i class="bi bi-envelope text-info"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">E-posta</small>
                                <a href="mailto:{{ $firm->contact_email }}" class="fw-medium text-decoration-none">
                                    {{ $firm->contact_email }}
                                </a>
                            </div>
                        </div>
                        @endif
                        
                        @if($firm->tax_no)
                        <div class="d-flex align-items-center gap-3">
                            <div class="contact-icon bg-secondary bg-opacity-10">
                                <i class="bi bi-upc text-secondary"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Vergi No</small>
                                <span class="fw-medium font-monospace">{{ $firm->tax_no }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                    
                    @if ($firm->notes)
                    <hr class="my-3">
                    <div class="bg-light rounded p-3">
                        <small class="text-muted d-block mb-1">
                            <i class="bi bi-sticky me-1"></i>Notlar
                        </small>
                        <p class="mb-0 small">{{ $firm->notes }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Fiyat Geçmişi --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-currency-exchange text-success"></i>
                        <h6 class="mb-0">Fiyat Geçmişi</h6>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="collapse" data-bs-target="#priceHistoryForm">
                        <i class="bi bi-plus"></i>
                    </button>
                </div>
                <div class="collapse" id="priceHistoryForm">
                    <div class="card-body border-bottom bg-light">
                        <form action="{{ route('firms.price-histories.store', $firm) }}" method="POST" class="row g-2">
                            @csrf
                            <div class="col-6">
                                <label class="form-label small">Başlangıç</label>
                                <input type="date" name="valid_from" value="{{ old('valid_from', now()->format('Y-m-d')) }}"
                                       class="form-control form-control-sm @error('valid_from') is-invalid @enderror" required>
                            </div>
                            <div class="col-6">
                                <label class="form-label small">Ücret (₺)</label>
                                <input type="number" step="0.01" min="0" name="amount" value="{{ old('amount', $firm->monthly_fee) }}"
                                       class="form-control form-control-sm @error('amount') is-invalid @enderror" required>
                            </div>
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary btn-sm w-100">Ekle</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        @forelse ($firm->priceHistories as $history)
                        <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                            <div>
                                <span class="fw-medium">{{ Format::money($history->amount) }}</span>
                                <small class="text-muted ms-2">{{ $history->valid_from?->format('d.m.Y') }}'den itibaren</small>
                            </div>
                            <form action="{{ route('firms.price-histories.destroy', [$firm, $history]) }}" method="POST"
                                  onsubmit="return confirm('Silmek istediğinize emin misiniz?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-link text-danger p-0">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                        </div>
                        @empty
                        <div class="list-group-item text-center text-muted py-3">
                            <small>Fiyat geçmişi yok</small>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Vergi Beyannameleri Kartı --}}
            @include('firms._tax_declarations_card')
            
            {{-- Atanan Vergi Formları --}}
            @include('firms._tax_forms_section')
            
            {{-- Hesap Ekstresi --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex align-items-center gap-2">
                    <i class="bi bi-file-earmark-pdf text-danger"></i>
                    <h6 class="mb-0">Hesap Ekstresi</h6>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">Başlangıç</label>
                            <input type="date" id="statement_start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}"
                                   class="form-control form-control-sm" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted mb-1">Bitiş</label>
                            <input type="date" id="statement_end_date" value="{{ now()->format('Y-m-d') }}"
                                   class="form-control form-control-sm" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted mb-1">E-posta (Gönder için)</label>
                            <input type="email" id="statement_send_to" value="{{ $firm->contact_email }}"
                                   class="form-control form-control-sm" placeholder="E-posta adresi">
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-danger btn-sm w-100" id="btnDownloadPdf" onclick="downloadStatementPdf()">
                                <i class="bi bi-file-pdf me-1"></i>PDF
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-primary btn-sm w-100" onclick="sendStatementEmail()">
                                <i class="bi bi-envelope me-1"></i>Gönder
                            </button>
                        </div>
                        <div class="col-4">
                            <button type="button" class="btn btn-outline-secondary btn-sm w-100" onclick="printStatement()">
                                <i class="bi bi-printer me-1"></i>Yazdır
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            
            {{-- E-posta ve Yazdır için hidden form --}}
            <form id="statementFormHidden" action="{{ route('firms.statement', $firm) }}" method="POST" style="display:none;">
                @csrf
                <input type="hidden" name="start_date" id="hidden_start_date">
                <input type="hidden" name="end_date" id="hidden_end_date">
                <input type="hidden" name="send_to" id="hidden_send_to">
                <input type="hidden" name="action" id="hidden_action">
            </form>
            
            <script>
            // PDF İndir - Fetch API ile blob olarak
            async function downloadStatementPdf() {
                const startDate = document.getElementById('statement_start_date').value;
                const endDate = document.getElementById('statement_end_date').value;
                
                if (!startDate || !endDate) {
                    alert('Lütfen tarih aralığı seçin.');
                    return;
                }
                
                const btn = document.getElementById('btnDownloadPdf');
                const originalHtml = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Yükleniyor...';
                btn.disabled = true;
                
                try {
                    const formData = new FormData();
                    formData.append('_token', '{{ csrf_token() }}');
                    formData.append('start_date', startDate);
                    formData.append('end_date', endDate);
                    formData.append('action', 'download');
                    
                    const response = await fetch('{{ route('firms.statement', $firm) }}', {
                        method: 'POST',
                        body: formData
                    });
                    
                    if (!response.ok) {
                        throw new Error('PDF oluşturulamadı');
                    }
                    
                    const blob = await response.blob();
                    const url = window.URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = 'hesap-ekstresi-{{ $firm->id }}-' + startDate.replace(/-/g, '') + '-' + endDate.replace(/-/g, '') + '.pdf';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    a.remove();
                } catch (error) {
                    alert('PDF indirirken hata oluştu: ' + error.message);
                } finally {
                    btn.innerHTML = originalHtml;
                    btn.disabled = false;
                }
            }
            
            // E-posta Gönder
            function sendStatementEmail() {
                const email = document.getElementById('statement_send_to').value;
                if (!email) {
                    alert('Lütfen e-posta adresi girin.');
                    return;
                }
                
                document.getElementById('hidden_start_date').value = document.getElementById('statement_start_date').value;
                document.getElementById('hidden_end_date').value = document.getElementById('statement_end_date').value;
                document.getElementById('hidden_send_to').value = email;
                document.getElementById('hidden_action').value = 'email';
                document.getElementById('statementFormHidden').target = '_self';
                document.getElementById('statementFormHidden').submit();
            }
            
            // Yazdır
            function printStatement() {
                document.getElementById('hidden_start_date').value = document.getElementById('statement_start_date').value;
                document.getElementById('hidden_end_date').value = document.getElementById('statement_end_date').value;
                document.getElementById('hidden_send_to').value = '';
                document.getElementById('hidden_action').value = 'print';
                document.getElementById('statementFormHidden').target = '_blank';
                document.getElementById('statementFormHidden').submit();
            }
            </script>
        </div>

        {{-- Sağ Kolon: Cari Hareketler --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-list-ul text-primary"></i>
                            <h6 class="mb-0">Cari Hareketler</h6>
                        </div>
                        <span class="badge bg-light text-dark">{{ $firm->transactions->count() }} kayıt</span>
                    </div>
                </div>
                <div class="card-body">
                    {{-- Özet Kartları --}}
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <div class="summary-box summary-box-danger">
                                <div class="summary-icon">
                                    <i class="bi bi-arrow-up-right"></i>
                                </div>
                                <div>
                                    <div class="summary-label">Toplam Borç</div>
                                    <div class="summary-value">{{ Format::money($debitTotal) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-box summary-box-success">
                                <div class="summary-icon">
                                    <i class="bi bi-arrow-down-left"></i>
                                </div>
                                <div>
                                    <div class="summary-label">Toplam Tahsilat</div>
                                    <div class="summary-value">{{ Format::money($creditTotal) }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="summary-box {{ $balance > 0 ? 'summary-box-warning' : 'summary-box-info' }}">
                                <div class="summary-icon">
                                    <i class="bi bi-wallet2"></i>
                                </div>
                                <div>
                                    <div class="summary-label">Net Bakiye</div>
                                    <div class="summary-value">{{ Format::money($balance) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if ($firm->transactions->isEmpty())
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted d-block mb-3"></i>
                            <p class="text-muted mb-0">Henüz hareket bulunmuyor.</p>
                            <a href="{{ route('invoices.create', ['firm_id' => $firm->id]) }}" class="btn btn-primary mt-3">
                                <i class="bi bi-plus me-1"></i>İlk Faturayı Oluştur
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 100px;">Tarih</th>
                                        <th>Açıklama</th>
                                        <th class="text-center" style="width: 80px;">Tip</th>
                                        <th class="text-end" style="width: 120px;">Tutar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($firm->transactions->take(20) as $transaction)
                                        @php
                                            $url = null;
                                            if ($transaction->sourceable) {
                                                if ($transaction->sourceable instanceof \App\Models\Invoice) {
                                                    $url = route('invoices.show', $transaction->sourceable);
                                                } elseif ($transaction->sourceable instanceof \App\Models\Payment) {
                                                    $url = route('payments.index', ['firm_id' => $firm->id]);
                                                }
                                            }
                                        @endphp
                                        <tr class="{{ $url ? 'table-row-clickable' : '' }}" 
                                            @if($url) onclick="window.location='{{ $url }}'" @endif>
                                            <td>
                                                <small class="text-muted">{{ $transaction->date?->format('d.m.Y') }}</small>
                                            </td>
                                            <td>
                                                {{ $transaction->description }}
                                                @if ($transaction->sourceable)
                                                    <span class="badge bg-light text-muted ms-1">
                                                        #{{ $transaction->sourceable->id }}
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($transaction->type === 'debit')
                                                    <span class="badge bg-danger-subtle text-danger">Borç</span>
                                                @else
                                                    <span class="badge bg-success-subtle text-success">Alacak</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                <span class="fw-semibold {{ $transaction->type === 'debit' ? 'text-danger' : 'text-success' }}">
                                                    {{ $transaction->type === 'debit' ? '+' : '-' }}{{ Format::money($transaction->amount) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($firm->transactions->count() > 20)
                        <div class="text-center mt-3">
                            <small class="text-muted">{{ $firm->transactions->count() - 20 }} hareket daha var</small>
                        </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Firma Header */
.firm-header-gradient {
    background: linear-gradient(135deg, #1e3a5f 0%, #2d5a87 50%, #3d7ab3 100%);
}

.firm-avatar {
    width: 64px;
    height: 64px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 16px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    font-weight: 700;
    color: white;
    backdrop-filter: blur(10px);
}

.firm-stat-box {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border-radius: 12px;
    padding: 1rem;
}

.firm-stat-value {
    font-size: 1.1rem;
    font-weight: 700;
}

.firm-stat-label {
    font-size: 0.7rem;
    color: rgba(255, 255, 255, 0.7);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* İletişim İkonları */
.contact-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1rem;
}

/* Özet Kutuları */
.summary-box {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-radius: 12px;
}

.summary-box-danger {
    background: linear-gradient(135deg, rgba(220, 53, 69, 0.1) 0%, rgba(220, 53, 69, 0.05) 100%);
    border-left: 3px solid #dc3545;
}

.summary-box-success {
    background: linear-gradient(135deg, rgba(25, 135, 84, 0.1) 0%, rgba(25, 135, 84, 0.05) 100%);
    border-left: 3px solid #198754;
}

.summary-box-warning {
    background: linear-gradient(135deg, rgba(255, 193, 7, 0.1) 0%, rgba(255, 193, 7, 0.05) 100%);
    border-left: 3px solid #ffc107;
}

.summary-box-info {
    background: linear-gradient(135deg, rgba(13, 110, 253, 0.1) 0%, rgba(13, 110, 253, 0.05) 100%);
    border-left: 3px solid #0d6efd;
}

.summary-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.25rem;
}

.summary-box-danger .summary-icon { background: rgba(220, 53, 69, 0.15); color: #dc3545; }
.summary-box-success .summary-icon { background: rgba(25, 135, 84, 0.15); color: #198754; }
.summary-box-warning .summary-icon { background: rgba(255, 193, 7, 0.15); color: #997404; }
.summary-box-info .summary-icon { background: rgba(13, 110, 253, 0.15); color: #0d6efd; }

.summary-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-transform: uppercase;
}

.summary-value {
    font-size: 1.25rem;
    font-weight: 700;
}

.summary-box-danger .summary-value { color: #dc3545; }
.summary-box-success .summary-value { color: #198754; }
.summary-box-warning .summary-value { color: #997404; }
.summary-box-info .summary-value { color: #0d6efd; }

/* Tablo satırı tıklanabilir */
.table-row-clickable {
    cursor: pointer;
    transition: background-color 0.2s;
}

.table-row-clickable:hover {
    background-color: #f8f9fa !important;
}

/* Responsive */
@media (max-width: 991px) {
    .firm-header-gradient {
        text-align: center;
    }
    
    .firm-avatar {
        margin: 0 auto;
    }
}
</style>
@endsection
