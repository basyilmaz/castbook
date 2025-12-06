<?php

namespace App\Http\Controllers;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Setting;
use App\Support\Format;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->only(['firm_id', 'month', 'per_page']);
        $perPage = (int) ($filters['per_page'] ?? 10);
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }
        $filters['per_page'] = $perPage;

        $payments = Payment::query()
            ->with(['firm', 'invoice'])
            ->when($filters['firm_id'] ?? null, fn ($query, $firmId) => $query->where('firm_id', $firmId))
            ->when($filters['month'] ?? null, function ($query, $month) {
                try {
                    $date = Carbon::createFromFormat('Y-m', $month);
                } catch (\Exception $exception) {
                    return;
                }

                $query->whereYear('date', $date->year)->whereMonth('date', $date->month);
            })
            ->latest('date')
            ->paginate($perPage)
            ->withQueryString();

        $firms = Firm::orderBy('name')->get(['id', 'name']);

        return view('payments.index', [
            'payments' => $payments,
            'firms' => $firms,
            'filters' => $filters,
            'perPage' => $perPage,
        ]);
    }

    public function create(Request $request): View
    {
        $firms = Firm::active()->orderBy('name')->get();
        $selectedFirmId = $request->integer('firm_id') ?: old('firm_id');
        $selectedInvoiceId = $request->integer('invoice_id') ?: old('invoice_id');
        $invoices = collect();

        if ($selectedFirmId) {
            $invoices = Invoice::query()
                ->where('firm_id', $selectedFirmId)
                ->outstanding()
                ->withSum('payments', 'amount')
                ->orderByDesc('date')
                ->get()
                ->map(function (Invoice $invoice) {
                    $invoice->remaining_amount = max(
                        0,
                        (float) $invoice->amount - (float) ($invoice->payments_sum_amount ?? 0)
                    );

                    return $invoice;
                })
                ->filter(fn (Invoice $invoice) => $invoice->remaining_amount > 0.009)
                ->values();
        }

        $payment = new Payment([
            'date' => Carbon::now(),
            'amount' => 0,
        ]);

        $firmSummary = null;
        if ($selectedFirmId) {
            $firm = Firm::with(['payments' => fn ($query) => $query->latest('date')])
                ->find($selectedFirmId);

            if ($firm) {
                $latestPayment = $firm->payments->first();

                $debit = $firm->transactions()->debits()->sum('amount');
                $credit = $firm->transactions()->credits()->sum('amount');

                $firmSummary = [
                    'name' => $firm->name,
                    'balance' => $debit - $credit,
                    'last_payment_at' => optional($latestPayment?->date)->format('d.m.Y'),
                ];
            }
        }

        $paymentMethods = Setting::getPaymentMethods();

        return view('payments.create', compact(
            'payment',
            'firms',
            'selectedFirmId',
            'selectedInvoiceId',
            'invoices',
            'firmSummary',
            'paymentMethods'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);

        $invoice = null;
        if (! empty($data['invoice_id'])) {
            $invoice = Invoice::query()
                ->where('id', $data['invoice_id'])
                ->where('firm_id', $data['firm_id'])
                ->withSum('payments', 'amount')
                ->firstOrFail();

            $remaining = max(
                0,
                (float) $invoice->amount - (float) ($invoice->payments_sum_amount ?? 0)
            );

            if ($remaining <= 0) {
                return back()
                    ->withErrors([
                        'invoice_id' => 'Bu faturanın borcu kalmamış görünüyor.',
                    ])
                    ->withInput();
            }

            if ($data['amount'] > $remaining + 0.01) {
                return back()
                    ->withErrors([
                        'amount' => 'Girilen tutar kalan borç tutarını aşıyor. Maksimum ' . Format::money($remaining) . ' girilebilir.',
                    ])
                    ->withInput();
            }
        }

        $payment = Payment::create($data);

        $payment->transactions()->create([
            'firm_id' => $payment->firm_id,
            'type' => 'credit',
            'amount' => $payment->amount,
            'date' => $payment->date,
            'description' => $payment->note
                ? "Tahsilat: {$payment->note}"
                : 'Tahsilat kaydı',
        ]);

        if ($invoice) {
            $invoice->refreshPaymentStatus();
        }

        return redirect()
            ->route('payments.index')
            ->with('status', 'Tahsilat kaydedildi ve cari hesap güncellendi.');
    }

    public function destroy(Payment $payment): RedirectResponse
    {
        $invoice = $payment->invoice;

        $payment->transactions()->delete();
        $payment->delete();

        if ($invoice) {
            $invoice->refreshPaymentStatus();
        }

        return redirect()
            ->route('payments.index')
            ->with('status', 'Tahsilat kaydı silindi.');
    }

    protected function validatedData(Request $request): array
    {
        $methods = Setting::getPaymentMethods();

        return $request->validate([
            'firm_id' => ['required', 'exists:firms,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'method' => ['required', 'string', 'max:100', Rule::in($methods)],
            'date' => ['required', 'date'],
            'reference' => ['nullable', 'string', 'max:100'],
            'note' => ['nullable', 'string'],
        ]);
    }
}
