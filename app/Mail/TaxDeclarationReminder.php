<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TaxDeclarationReminder extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  \Illuminate\Support\Collection  $declarations
     */
    public function __construct(
        public $declarations,
        public int $daysUntilDue,
        public string $type = 'upcoming' // upcoming veya overdue
    ) {
    }

    public function envelope(): Envelope
    {
        $subject = $this->type === 'overdue' 
            ? "🚨 Gecikmiş Beyannameler - Acil İşlem Gerekli"
            : "🗓️ Yaklaşan Beyanname Bildirimi ({$this->daysUntilDue} gün)";

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tax-declaration-reminder',
            with: [
                'declarations' => $this->declarations,
                'daysUntilDue' => $this->daysUntilDue,
                'type' => $this->type,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
