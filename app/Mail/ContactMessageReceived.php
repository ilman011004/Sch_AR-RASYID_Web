<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Models\School;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactMessageReceived extends Mailable
{
    public function __construct(public ContactMessage $contact, public School $school)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[Kontak '.$this->school->name.'] Pesan dari '.$this->contact->name,
            // Menekan "Balas" di email langsung menjawab ke pengirim
            replyTo: $this->contact->email
                ? [new Address($this->contact->email, $this->contact->name)]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-message');
    }
}