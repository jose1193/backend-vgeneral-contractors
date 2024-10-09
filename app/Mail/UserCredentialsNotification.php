<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class UserCredentialsNotification extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(
        public $user,
        public $password,
        public $roles,
        public $isNewUser
    ) {
        $this->user = $user;
        $this->password = $password;
        $this->roles = $roles;
        $this->isNewUser = $isNewUser;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subject = $this->isNewUser
            ? 'Welcome to V General Contractors - Your Account Credentials'
            : 'V General Contractors - Your Password Has Been Updated';

        return new Envelope(
            subject: $subject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.user_credentials_notification',
            with: [
                'username' => $this->user->username,
                'email' => $this->user->email,
                'password' => $this->password,
                'roles' => $this->roles,
                'isNewUser' => $this->isNewUser,
            ],
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
