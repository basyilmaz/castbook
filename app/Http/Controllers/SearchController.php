<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Payment;
use App\Support\Format;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Global arama - Firma, Fatura, Tahsilat
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->input('q', '');
        
        if (strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $results = [];
        $limit = 5; // Her kategoriden max 5 sonuç

        // Firma ara
        $firms = Firm::where('name', 'like', "%{$query}%")
            ->orWhere('tax_no', 'like', "%{$query}%")
            ->orWhere('contact_person', 'like', "%{$query}%")
            ->orWhere('contact_email', 'like', "%{$query}%")
            ->limit($limit)
            ->get();

        foreach ($firms as $firm) {
            $results[] = [
                'type' => 'firm',
                'type_label' => 'Firma',
                'icon' => 'bi-building',
                'title' => $firm->name,
                'subtitle' => $firm->tax_no ? "VKN: {$firm->tax_no}" : ($firm->contact_person ?? ''),
                'url' => route('firms.show', $firm),
                'badge' => $firm->status === 'active' ? 'Aktif' : 'Pasif',
                'badge_class' => $firm->status === 'active' ? 'success' : 'secondary',
            ];
        }

        // Fatura ara (resmi numara veya açıklama)
        $invoices = Invoice::with('firm:id,name')
            ->where('official_number', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%")
            ->orWhereHas('firm', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();

        foreach ($invoices as $invoice) {
            $results[] = [
                'type' => 'invoice',
                'type_label' => 'Fatura',
                'icon' => 'bi-receipt',
                'title' => $invoice->firm->name ?? 'Firma Yok',
                'subtitle' => ($invoice->official_number ? "#{$invoice->official_number} - " : '') . Format::money($invoice->amount),
                'url' => route('invoices.show', $invoice),
                'badge' => $this->getInvoiceStatusLabel($invoice->status),
                'badge_class' => $this->getInvoiceStatusClass($invoice->status),
            ];
        }

        // Tahsilat ara (not veya yöntem)
        $payments = Payment::with('firm:id,name')
            ->where('notes', 'like', "%{$query}%")
            ->orWhere('method', 'like', "%{$query}%")
            ->orWhereHas('firm', function ($q) use ($query) {
                $q->where('name', 'like', "%{$query}%");
            })
            ->limit($limit)
            ->get();

        foreach ($payments as $payment) {
            $results[] = [
                'type' => 'payment',
                'type_label' => 'Tahsilat',
                'icon' => 'bi-cash-coin',
                'title' => $payment->firm->name ?? 'Firma Yok',
                'subtitle' => Format::money($payment->amount) . ' - ' . ($payment->method ?? 'Belirtilmemiş'),
                'url' => route('payments.index', ['firm_id' => $payment->firm_id]),
                'badge' => optional($payment->date)->format('d.m.Y'),
                'badge_class' => 'info',
            ];
        }

        return response()->json([
            'results' => $results,
            'total' => count($results),
            'query' => $query,
        ]);
    }

    protected function getInvoiceStatusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Ödendi',
            'partial' => 'Kısmi',
            'unpaid' => 'Ödenmedi',
            default => $status,
        };
    }

    protected function getInvoiceStatusClass(string $status): string
    {
        return match ($status) {
            'paid' => 'success',
            'partial' => 'warning',
            'unpaid' => 'danger',
            default => 'secondary',
        };
    }
}
