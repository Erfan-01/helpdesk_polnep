<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TicketCreatedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public string $fullName,
        public string $serviceName,
        public string $requestNumber,
        public string $status,
        public string $estimatedResponse
    ) {
    }


    public function envelope(): Envelope
    {
        return new Envelope(
            subject:
                'Tiket Helpdesk POLNEP - '
                . $this->requestNumber,
        );
    }


    public function content(): Content
    {
        return new Content(
            view: 'emails.ticket-created',
        );
    }


    public function attachments(): array
    {
        return [];
    }
}