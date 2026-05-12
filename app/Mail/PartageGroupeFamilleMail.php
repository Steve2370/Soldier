<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PartageGroupeFamilleMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $expediteur,
        public User $destinataire,
        public string $nomCoffre,
        public string $lienAcceptation,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->expediteur->name . ' a partagé un accès dans le groupe famille'
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.partage-groupe-famille');
    }
}
