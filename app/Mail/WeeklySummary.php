<?php

namespace App\Mail;

use App\Models\Firm;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\TaxDeclaration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;

class WeeklySummary extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public array $stats;
    public array $upcomingDeclarations;
    public array $overdueInvoices;
    public array $recentPayments;

    public function __construct()
    {
        $this->prepareStats();
        $this->prepareUpcomingDeclarations();
        $this->prepareOverdueInvoices();
        $this->prepareRecentPayments();
    }

    protected function prepareStats(): void
    {
        $lastWeek = Carbon::now()->subWeek();
        
        $this->stats = [
            'total_firms' => Firm::active()->count(),
            'new_invoices_count' => Invoice::where('created_at', '>=', $lastWeek)->count(),
            'new_invoices_amount' => Invoice::where('created_at', '>=', $lastWeek)->sum('amount'),
            'payments_count' => Payment::where('created_at', '>=', $lastWeek)->count(),
            'payments_amount' => Payment::where('created_at', '>=', $lastWeek)->sum('amount'),
            'pending_invoices_count' => Invoice::whereIn('status', ['unpaid', 'partial'])->count(),
            'pending_invoices_amount' => Invoice::whereIn('status', ['unpaid', 'partial'])->sum('amount'),
        ];
    }

    protected function prepareUpcomingDeclarations(): void
    {
        $this->upcomingDeclarations = TaxDeclaration::with(['firm', 'taxForm'])
            ->where('status', 'pending')
            ->whereBetween('due_date', [now(), now()->addDays(14)])
            ->orderBy('due_date')
            ->limit(10)
            ->get()
            ->map(fn ($d) => [
                'firm' => $d->firm->name ?? '-',
                'form' => $d->taxForm->code ?? '-',
                'due_date' => $d->due_date->format('d.m.Y'),
                'days_left' => (int) now()->diffInDays($d->due_date, false),
            ])
            ->toArray();
    }

    protected function prepareOverdueInvoices(): void
    {
        $this->overdueInvoices = Invoice::with('firm')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where(function ($q) {
                $q->whereNotNull('due_date')->where('due_date', '<', now())
                  ->orWhere(function ($inner) {
                      $inner->whereNull('due_date')->where('date', '<', now()->subMonth());
                  });
            })
            ->orderBy('due_date')
            ->limit(10)
            ->get()
            ->map(fn ($i) => [
                'firm' => $i->firm->name ?? '-',
                'invoice_no' => $i->official_number ?? '#' . $i->id,
                'amount' => number_format($i->amount, 2, ',', '.'),
                'due_date' => $i->due_date?->format('d.m.Y') ?? '-',
            ])
            ->toArray();
    }

    protected function prepareRecentPayments(): void
    {
        $this->recentPayments = Payment::with(['firm', 'invoice'])
            ->where('created_at', '>=', now()->subWeek())
            ->orderByDesc('created_at')
            ->limit(10)
            ->get()
            ->map(fn ($p) => [
                'firm' => $p->firm->name ?? '-',
                'amount' => number_format($p->amount, 2, ',', '.'),
                'date' => $p->date?->format('d.m.Y') ?? $p->created_at->format('d.m.Y'),
            ])
            ->toArray();
    }

    public function envelope(): Envelope
    {
        $weekRange = now()->startOfWeek()->format('d.m') . ' - ' . now()->endOfWeek()->format('d.m.Y');
        
        return new Envelope(
            subject: "Haftalık Özet Raporu ({$weekRange})",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-summary',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
