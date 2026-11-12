<?php

namespace App\Mail;

use App\Models\ContactMessage;
use App\Models\School;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class ContactMessageReply extends Mailable
{
    public function __construct(public ContactMessage $contact, public School $school)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Balasan dari '.$this->school->name,
            // Bila pengguna menekan "Balas", jawabannya kembali ke email sekolah jenjang ini
            replyTo: filled($this->school->email)
                ? [new Address($this->school->email, $this->school->name)]
                : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-reply');
    }
}