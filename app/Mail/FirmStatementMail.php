<?php

namespace App\Mail;

use App\Models\Firm;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class FirmStatementMail extends Mailable
{
    use Queueable, SerializesModels;

    protected Firm $firm;
    protected array $summary;
    protected $transactions;
    protected string $pdfContent;
    protected string $fileName;
    protected array $settings;

    public function __construct(Firm $firm, array $summary, $transactions, string $pdfContent, string $fileName, array $settings)
    {
        $this->firm = $firm;
        $this->summary = $summary;
        $this->transactions = $transactions;
        $this->pdfContent = $pdfContent;
        $this->fileName = $fileName;
        $this->settings = $settings;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Hesap Ekstresi - ' . $this->firm->name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.firm_statement',
            with: [
                'firm' => $this->firm,
                'summary' => $this->summary,
                'settings' => $this->settings,
            ]
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [
            Attachment::fromData(fn () => $this->pdfContent, $this->fileName)->withMime('application/pdf'),
        ];
    }
}
