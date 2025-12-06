<?php

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use App\Support\Format;

class MonthlyInvoiceSummary extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array<string, mixed>>  $invoices
     */
    public function __construct(
        public Carbon $period,
        public int $createdCount,
        public array $invoices,
        public float $totalAmount
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->period->format('m/Y') . ' Fatura Otomasyonu Ã–zeti';

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.invoices.summary',
            with: [
                'period' => $this->period,
                'createdCount' => $this->createdCount,
                'invoices' => $this->invoices,
                'totalAmount' => $this->totalAmount,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }

    public function render(): string
    {
        $html = parent::render();

        $html .= sprintf(
            '<!-- Toplam <strong>%s</strong> adet fatura oluÅŸturuldu. -->',
            $this->createdCount
        );

        $html .= sprintf(
            '<!-- Toplam tutar: <strong>%s</strong> -->',
            Format::money($this->totalAmount)
        );

        return $html;
    }
}
