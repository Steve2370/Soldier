<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AdminMailMasse extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $sujet,
        public string $contenu,
        public string $destinataireName = '',
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->sujet);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-mail-masse');
    }
}
