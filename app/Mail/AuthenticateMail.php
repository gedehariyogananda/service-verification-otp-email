<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AuthenticateMail extends Mailable
{
    use Queueable, SerializesModels;

    public $get_user_email;
    public $get_user_name;
    public $validTokenRegister;



    /**
     * Create a new message instance.
     */
    public function __construct($get_user_email, $get_user_name, $validTokenRegister)
    {
        $this->get_user_email = $get_user_email;
        $this->get_user_name = $get_user_name;
        $this->validTokenRegister = $validTokenRegister;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Email Verification Code : ' . $this->validTokenRegister,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.email',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
