<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MembreInviteFamilleMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $proprietaire,
        public User $membre,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: $this->proprietaire->name . ' vous a ajouté à son groupe Soldier Famille');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.membre-invite-famille');
    }
}
