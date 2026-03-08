<?php

namespace App\Mail; // This will be set correctly by Artisan

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue; // If you plan to queue emails
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Notifications\Messages\MailMessage; // While this is for Notifications, Mailables use their own MailMessage concepts
                                                // For a Mailable, you typically use ->subject(), ->line(), ->view() directly
                                                // Let's refine this below.

class VerifyEmailWithCode extends Mailable // This will be set correctly by Artisan
{
    use Queueable, SerializesModels;

    public $code; // Property to hold the verification code

    /**
     * Create a new message instance.
     *
     * @param string $code
     * @return void
     */
    public function __construct(string $code)
    {
        $this->code = $code;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // This is where you define the email content and subject.
        // It will be similar to the 'toMail' method from a Notification.
        return $this->subject('Your Email Verification Code')
                    ->view('emails.verify-code') // Create this Blade view file in resources/views/emails/
                    ->with([
                        'code' => $this->code,
                        // Add any other data you want to pass to the email view
                    ]);
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