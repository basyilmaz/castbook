<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Support\Format;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  \Illuminate\Support\Collection<int, Invoice>  $invoices
     */
    public function __construct(
        public $invoices,
        public string $firmName,
        public float $totalAmount,
        public string $type = 'upcoming' // upcoming veya overdue
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->type === 'overdue' 
            ? "⚠️ Gecikmiş Ödeme Hatırlatması - {$this->firmName}"
            : "📅 Yaklaşan Ödeme Hatırlatması - {$this->firmName}";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-reminder',
            with: [
                'invoices' => $this->invoices,
                'firmName' => $this->firmName,
                'totalAmount' => $this->totalAmount,
                'type' => $this->type,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
