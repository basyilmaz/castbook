{{-- Fatura Satır Kalemleri (Line Items) --}}
@php
    use App\Support\Format;
    $existingItems = $invoice->lineItems ?? collect();
@endphp

<div class="col-12 mt-4">
    <div class="card border">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0">
                <i class="bi bi-list-ul me-2"></i>Fatura Kalemleri
            </h6>
            <button type="button" class="btn btn-sm btn-outline-primary" id="addLineItem">
                <i class="bi bi-plus-lg me-1"></i>Satır Ekle
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm align-middle mb-0" id="lineItemsTable">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40%">Açıklama</th>
                            <th style="width: 15%" class="text-center">Miktar</th>
                            <th style="width: 20%" class="text-end">Birim Fiyat</th>
                            <th style="width: 20%" class="text-end">Tutar</th>
                            <th style="width: 5%" class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="lineItemsBody">
                        @if($existingItems->isNotEmpty())
                            @foreach($existingItems as $index => $item)
                                <tr class="line-item-row" data-index="{{ $index }}">
                                    <td>
                                        <input type="hidden" name="line_items[{{ $index }}][id]" value="{{ $item->id }}">
                                        <input type="text" 
                                               name="line_items[{{ $index }}][description]" 
                                               class="form-control form-control-sm line-description" 
                                               value="{{ old("line_items.$index.description", $item->description) }}"
                                               placeholder="Hizmet/Ürün açıklaması"
                                               required>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="line_items[{{ $index }}][quantity]" 
                                               class="form-control form-control-sm text-center line-quantity" 
                                               value="{{ old("line_items.$index.quantity", $item->quantity) }}"
                                               min="0.01" step="0.01" required>
                                    </td>
                                    <td>
                                        <input type="number" 
                                               name="line_items[{{ $index }}][unit_price]" 
                                               class="form-control form-control-sm text-end line-unit-price" 
                                               value="{{ old("line_items.$index.unit_price", $item->unit_price) }}"
                                               min="0" step="0.01" required>
                                    </td>
                                    <td class="text-end">
                                        <span class="line-amount fw-medium">{{ Format::money($item->amount) }}</span>
                                        <input type="hidden" name="line_items[{{ $index }}][amount]" 
                                               class="line-amount-input" value="{{ $item->amount }}">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-line-item" 
                                                title="Satırı Sil">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        @endif
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="3" class="text-end fw-bold">Genel Toplam:</td>
                            <td class="text-end">
                                <span class="fw-bold text-primary fs-5" id="grandTotal">₺ 0,00</span>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <div class="card-footer bg-light">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    Satır eklemek için "Satır Ekle" butonuna tıklayın. Toplam otomatik hesaplanır.
                </small>
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="syncTotalAmount" checked>
                    <label class="form-check-label small" for="syncTotalAmount">
                        Toplam tutarı otomatik güncelle
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Line Item Template (JavaScript'te kullanılacak) --}}
<template id="lineItemTemplate">
    <tr class="line-item-row" data-index="__INDEX__">
        <td>
            <input type="text" 
                   name="line_items[__INDEX__][description]" 
                   class="form-control form-control-sm line-description" 
                   placeholder="Hizmet/Ürün açıklaması"
                   required>
        </td>
        <td>
            <input type="number" 
                   name="line_items[__INDEX__][quantity]" 
                   class="form-control form-control-sm text-center line-quantity" 
                   value="1"
                   min="0.01" step="0.01" required>
        </td>
        <td>
            <input type="number" 
                   name="line_items[__INDEX__][unit_price]" 
                   class="form-control form-control-sm text-end line-unit-price" 
                   value="0"
                   min="0" step="0.01" required>
        </td>
        <td class="text-end">
            <span class="line-amount fw-medium">₺ 0,00</span>
            <input type="hidden" name="line_items[__INDEX__][amount]" class="line-amount-input" value="0">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-danger remove-line-item" title="Satırı Sil">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const lineItemsBody = document.getElementById('lineItemsBody');
    const addLineItemBtn = document.getElementById('addLineItem');
    const template = document.getElementById('lineItemTemplate');
    const grandTotalEl = document.getElementById('grandTotal');
    const amountInput = document.getElementById('amount');
    const syncCheckbox = document.getElementById('syncTotalAmount');
    
    let lineIndex = lineItemsBody.querySelectorAll('.line-item-row').length;

    // Para formatı
    function formatMoney(value) {
        return '₺ ' + parseFloat(value).toLocaleString('tr-TR', {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    // Satır tutarını hesapla
    function calculateLineAmount(row) {
        const quantity = parseFloat(row.querySelector('.line-quantity').value) || 0;
        const unitPrice = parseFloat(row.querySelector('.line-unit-price').value) || 0;
        const amount = quantity * unitPrice;
        
        row.querySelector('.line-amount').textContent = formatMoney(amount);
        row.querySelector('.line-amount-input').value = amount.toFixed(2);
        
        return amount;
    }

    // KDV hesaplama elementleri
    const vatRateSelect = document.getElementById('vat_rate');
    const vatIncludedYes = document.getElementById('vat_included_yes');
    const vatIncludedNo = document.getElementById('vat_included_no');
    const subtotalInput = document.getElementById('subtotal');
    const vatAmountInput = document.getElementById('vat_amount');
    const subtotalDisplay = document.getElementById('subtotalDisplay');
    const vatAmountDisplay = document.getElementById('vatAmountDisplay');
    const totalWithVatDisplay = document.getElementById('totalWithVatDisplay');

    // Genel toplamı ve KDV'yi hesapla
    function calculateGrandTotal() {
        let lineTotal = 0;
        lineItemsBody.querySelectorAll('.line-item-row').forEach(row => {
            lineTotal += calculateLineAmount(row);
        });
        
        grandTotalEl.textContent = formatMoney(lineTotal);
        
        // KDV hesaplama
        const vatRate = parseFloat(vatRateSelect?.value || 0);
        const vatIncluded = vatIncludedYes?.checked ?? true;
        
        let subtotal, vatAmount, total;
        
        if (vatIncluded) {
            // KDV dahil: lineTotal toplam, subtotal ve vatAmount hesaplanır
            total = lineTotal;
            subtotal = lineTotal / (1 + vatRate / 100);
            vatAmount = total - subtotal;
        } else {
            // KDV hariç: lineTotal net tutar, toplam = net + kdv
            subtotal = lineTotal;
            vatAmount = lineTotal * vatRate / 100;
            total = subtotal + vatAmount;
        }
        
        // Görüntüleri güncelle
        if (subtotalDisplay) subtotalDisplay.textContent = formatMoney(subtotal);
        if (vatAmountDisplay) vatAmountDisplay.textContent = formatMoney(vatAmount);
        if (totalWithVatDisplay) totalWithVatDisplay.textContent = formatMoney(total);
        
        // Hidden inputları güncelle
        if (subtotalInput) subtotalInput.value = subtotal.toFixed(2);
        if (vatAmountInput) vatAmountInput.value = vatAmount.toFixed(2);
        
        // Ana tutar alanını güncelle (checkbox işaretliyse)
        if (syncCheckbox && syncCheckbox.checked && amountInput) {
            amountInput.value = total.toFixed(2);
        }
        
        return total;
    }

    // KDV değişikliklerini dinle
    if (vatRateSelect) vatRateSelect.addEventListener('change', calculateGrandTotal);
    if (vatIncludedYes) vatIncludedYes.addEventListener('change', calculateGrandTotal);
    if (vatIncludedNo) vatIncludedNo.addEventListener('change', calculateGrandTotal);

    // Yeni satır ekle
    addLineItemBtn.addEventListener('click', function() {
        const newRow = template.content.cloneNode(true);
        const tr = newRow.querySelector('tr');
        
        // Index'leri güncelle
        tr.dataset.index = lineIndex;
        tr.innerHTML = tr.innerHTML.replace(/__INDEX__/g, lineIndex);
        
        lineItemsBody.appendChild(newRow);
        lineIndex++;
        
        // Yeni satırdaki ilk inputa focus
        const firstInput = lineItemsBody.lastElementChild.querySelector('.line-description');
        if (firstInput) firstInput.focus();
        
        // Event listener'ları ekle
        attachRowListeners(lineItemsBody.lastElementChild);
        calculateGrandTotal();
    });

    // Satır silme
    function attachRowListeners(row) {
        row.querySelector('.remove-line-item').addEventListener('click', function() {
            if (lineItemsBody.querySelectorAll('.line-item-row').length > 1) {
                row.remove();
                calculateGrandTotal();
            } else {
                alert('En az bir satır kalmalıdır.');
            }
        });
        
        // Miktar ve birim fiyat değişiminde toplam güncelle
        row.querySelector('.line-quantity').addEventListener('input', calculateGrandTotal);
        row.querySelector('.line-unit-price').addEventListener('input', calculateGrandTotal);
    }

    // Mevcut satırlara listener ekle
    lineItemsBody.querySelectorAll('.line-item-row').forEach(attachRowListeners);

    // Eğer hiç satır yoksa otomatik bir tane ekle
    if (lineItemsBody.querySelectorAll('.line-item-row').length === 0) {
        addLineItemBtn.click();
    }

    // İlk yüklemede toplamı hesapla
    calculateGrandTotal();

    // Firma değiştiğinde varsayılan fiyatı ilk satıra ekle
    const firmSelect = document.getElementById('firm_id');
    if (firmSelect) {
        firmSelect.addEventListener('change', function() {
            const selectedOption = firmSelect.options[firmSelect.selectedIndex];
            if (selectedOption && selectedOption.value) {
                // Fiyatı option text'inden parse et (örn: "ABC Ltd (₺ 2.500,00)")
                const match = selectedOption.text.match(/₺\s*([\d.,]+)/);
                if (match) {
                    const price = parseFloat(match[1].replace('.', '').replace(',', '.'));
                    
                    // İlk satırın birim fiyatını güncelle
                    const firstRow = lineItemsBody.querySelector('.line-item-row');
                    if (firstRow) {
                        firstRow.querySelector('.line-unit-price').value = price.toFixed(2);
                        calculateGrandTotal();
                    }
                }
            }
        });
    }
});
</script>
@endpush
