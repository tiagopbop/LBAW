<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class MailModel extends Mailable
{
    public $mailData;

    // A
    public function __construct($mailData) {
        $this->mailData = $mailData;
    }

    // B
    public function envelope() {
        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS'), env('MAIL_FROM_NAME')),
            subject: 'LBAW Tutorial 01 - Send Email',
        );
    }

    // C
    public function content() {
        return new Content(
            view: 'emails.example',
        );
    }
}
